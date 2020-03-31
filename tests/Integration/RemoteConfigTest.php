<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Exception\RemoteConfig\ValidationFailed;
use Kreait\Firebase\Exception\RemoteConfig\VersionMismatch;
use Kreait\Firebase\Exception\RemoteConfig\VersionNotFound;
use Kreait\Firebase\RemoteConfig;
use Kreait\Firebase\RemoteConfig\Condition;
use Kreait\Firebase\RemoteConfig\ConditionalValue;
use Kreait\Firebase\RemoteConfig\Parameter;
use Kreait\Firebase\RemoteConfig\TagColor;
use Kreait\Firebase\RemoteConfig\Template;
use Kreait\Firebase\RemoteConfig\UpdateType;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Throwable;

/**
 * @internal
 */
class RemoteConfigTest extends IntegrationTestCase
{
    /** @var string */
    private $template = <<<CONFIG
{
    "conditions": [
        {
            "name": "lang_german",
            "expression": "device.language in ['de', 'de_AT', 'de_CH']",
            "tagColor": "ORANGE"
        },
        {
            "name": "lang_french",
            "expression": "device.language in ['fr', 'fr_CA', 'fr_CH']",
            "tagColor": "GREEN"
        }
    ],
    "parameters": {
        "welcome_message": {
            "defaultValue": {
                "value": "Welcome!"
            },
            "conditionalValues": {
                "lang_german": {
                    "value": "Willkommen!"
                },
                "lang_french": {
                    "value": "Bienvenu!"
                }
            },
            "description": "This is a welcome message"
        }
    }
}
CONFIG;

    /** @var RemoteConfig */
    private $remoteConfig;

    protected function setUp(): void
    {
        $this->remoteConfig = self::$factory->createRemoteConfig();
    }

    public function testForcePublishAndGet(): void
    {
        $template = RemoteConfig\Template::fromArray(\json_decode($this->template, true));

        $this->remoteConfig->publish($template);

        $version = $this->remoteConfig->get()->version();

        if (!$version) {
            $this->fail('The template has no version');
        }

        $this->assertTrue($version->updateType()->equalsTo(UpdateType::FORCED_UPDATE));
    }

    public function testPublishOutdatedConfig(): void
    {
        $initial = RemoteConfig\Template::fromArray(\json_decode($this->template, true));

        $this->remoteConfig->publish($initial);

        $published = $this->remoteConfig->get();

        $this->remoteConfig->publish($published);

        // The published template has now a different etag, so publishing it again
        // with our old etag value should fail
        $this->expectException(VersionMismatch::class);
        $this->remoteConfig->publish($published);
    }

    public function testWithFluidConfiguration(): void
    {
        $germanLanguageCondition = Condition::named('lang_german')
            ->withExpression("device.language in ['de', 'de_AT', 'de_CH']")
            ->withTagColor(TagColor::ORANGE);

        $frenchLanguageCondition = Condition::fromArray([
            'name' => 'lang_french',
            'expression' => "device.language in ['fr', 'fr_CA', 'fr_CH']",
            'tagColor' => TagColor::GREEN,
        ]);

        $germanWelcomeMessage = ConditionalValue::basedOn($germanLanguageCondition)->withValue('Willkommen!');
        $frenchWelcomeMessage = new ConditionalValue('lang_french', 'Bienvenu!');

        $welcomeMessageParameter = Parameter::named('welcome_message')
            ->withDefaultValue('Welcome!')
            ->withDescription('This is a welcome message')
            ->withConditionalValue($germanWelcomeMessage)
            ->withConditionalValue($frenchWelcomeMessage);

        $template = Template::new()
            ->withCondition($germanLanguageCondition)
            ->withCondition($frenchLanguageCondition)
            ->withParameter($welcomeMessageParameter);

        $this->remoteConfig->publish($template);
        $this->addToAssertionCount(1);
    }

    public function testValidateValidTemplate(): void
    {
        $template = Template::fromArray(\json_decode($this->template, true));

        $this->remoteConfig->validate($template);
        $this->addToAssertionCount(1);
    }

    public function testValidateInvalidTemplate(): void
    {
        $template = $this->templateWithTooManyParameters();

        $this->expectException(ValidationFailed::class);
        $this->remoteConfig->validate($template);
    }

    public function testPublishInvalidTemplate(): void
    {
        $version = $this->remoteConfig->get()->version();

        if (!$version) {
            $this->fail('The template has no version');
        }

        $currentVersionNumber = $version->versionNumber();

        $template = $this->templateWithTooManyParameters();

        try {
            $this->remoteConfig->validate($template);
            $this->fail('A '.ValidationFailed::class.' should have been thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(ValidationFailed::class, $e);
        }

        $refetchedVersion = $this->remoteConfig->get()->version();

        if (!$refetchedVersion) {
            $this->fail('The template has no version');
        }

        $this->assertTrue($currentVersionNumber->equalsTo($refetchedVersion->versionNumber()));
    }

    public function testRollback(): void
    {
        $initialVersion = $this->remoteConfig->get()->version();

        if (!$initialVersion) {
            $this->fail('The template has no version');
        }

        $initialVersionNumber = $initialVersion->versionNumber();

        $query = RemoteConfig\FindVersions::all()
            ->withLimit(2)
            ->upToVersion($initialVersionNumber);

        $targetVersionNumber = null;
        foreach ($this->remoteConfig->listVersions($query) as $version) {
            $versionNumber = $version->versionNumber();

            if (!$versionNumber->equalsTo($initialVersionNumber)) {
                $targetVersionNumber = $versionNumber;
            }
        }

        if (!$targetVersionNumber) {
            $this->fail('A previous version number should have been retrieved');
        }

        $this->remoteConfig->rollbackToVersion($targetVersionNumber);

        $newVersion = $this->remoteConfig->get()->version();

        if (!$newVersion) {
            $this->fail('The new template has no version');
        }

        $newVersionNumber = $newVersion->versionNumber();
        $rollbackSource = $newVersion->rollbackSource();

        if (!$rollbackSource) {
            $this->fail('The new template version has no rollback source');
        }

        $this->assertFalse($newVersionNumber->equalsTo($initialVersionNumber));
        $this->assertTrue($rollbackSource->equalsTo($targetVersionNumber));
    }

    public function testListVersionsWithoutFilters(): void
    {
        // We only need to know that the first returned value is a version,
        // no need to iterate through all of them
        foreach ($this->remoteConfig->listVersions() as $version) {
            $this->assertInstanceOf(RemoteConfig\Version::class, $version);

            return;
        }

        $this->fail('Expected a version to be returned, but got none');
    }

    public function testFindVersionsWithFilters(): void
    {
        $currentVersion = $this->remoteConfig->get()->version();

        if (!$currentVersion) {
            $this->fail('The new template has no version');
        }

        $currentVersionUpdateDate = $currentVersion->updatedAt();

        $query = [
            'startingAt' => $currentVersionUpdateDate->modify('-2 months'),
            'endingAt' => $currentVersionUpdateDate,
            'upToVersion' => $currentVersion->versionNumber(),
            'pageSize' => 1,
            'limit' => $limit = 2,
        ];

        $counter = 0;

        $versions = $this->remoteConfig->listVersions($query);
        foreach ($versions as $version) {
            ++$counter;

            // Protect us from an infinite loop
            if ($counter > $limit) {
                $this->fail('The query returned more values than expected');
            }
        }

        $this->assertLessThanOrEqual($limit, $counter);
    }

    public function testGetVersion(): void
    {
        $currentVersion = $this->remoteConfig->get()->version();

        if (!$currentVersion) {
            $this->fail('The template has no version');
        }

        $currentVersionNumber = $currentVersion->versionNumber();

        $check = $this->remoteConfig->getVersion($currentVersionNumber);

        $this->assertTrue($check->versionNumber()->equalsTo($currentVersionNumber));
    }

    public function testGetNonExistingVersion(): void
    {
        $currentVersion = $this->remoteConfig->get()->version();

        if (!$currentVersion) {
            $this->fail('The template has no version');
        }

        $nextButNonExisting = (int) (string) $currentVersion->versionNumber() + 100;

        $this->expectException(VersionNotFound::class);
        $this->remoteConfig->getVersion($nextButNonExisting);
    }

    private function templateWithTooManyParameters()
    {
        $template = Template::new();

        for ($i = 0; $i < 2001; ++$i) {
            $template = $template->withParameter(Parameter::named('i_'.$i));
        }

        return $template;
    }
}
