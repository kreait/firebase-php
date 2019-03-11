<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Exception\RemoteConfig\OperationAborted;
use Kreait\Firebase\Exception\RemoteConfig\ValidationFailed;
use Kreait\Firebase\RemoteConfig;
use Kreait\Firebase\RemoteConfig\Condition;
use Kreait\Firebase\RemoteConfig\ConditionalValue;
use Kreait\Firebase\RemoteConfig\Parameter;
use Kreait\Firebase\RemoteConfig\TagColor;
use Kreait\Firebase\RemoteConfig\Template;
use Kreait\Firebase\Tests\IntegrationTestCase;

class RemoteConfigTest extends IntegrationTestCase
{
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

    /**
     * @var RemoteConfig
     */
    private $remoteConfig;

    protected function setUp()
    {
        $this->remoteConfig = self::$firebase->getRemoteConfig();
    }

    public function testPublishAndGet()
    {
        $template = RemoteConfig\Template::fromArray(\json_decode($this->template, true));

        $etag = $this->remoteConfig->publish($template);

        $check = $this->remoteConfig->get();

        $this->assertSame($etag, $check->getEtag());
    }

    public function testPublishOutdatedConfig()
    {
        $initial = RemoteConfig\Template::fromArray(\json_decode($this->template, true));

        $initialEtag = $this->remoteConfig->publish($initial);

        $published = $this->remoteConfig->get();

        $this->assertSame($initialEtag, $published->getEtag());

        $this->remoteConfig->publish($published);

        $this->expectException(OperationAborted::class);
        $this->remoteConfig->publish($published);
    }

    public function testWithFluidConfiguration()
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
            ->withConditionalValue($frenchWelcomeMessage)
        ;

        $template = Template::new()
            ->withCondition($germanLanguageCondition)
            ->withCondition($frenchLanguageCondition)
            ->withParameter($welcomeMessageParameter)
        ;

        $this->remoteConfig->publish($template);

        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testValidateTemplate()
    {
        $current = $this->remoteConfig->get();

        $template = $this->templateWithTooManyParameters();

        try {
            $this->remoteConfig->validate($template);
            $this->fail('A '.ValidationFailed::class.' should have been thrown');
        } catch (\Throwable $e) {
            $this->assertInstanceOf(ValidationFailed::class, $e);
        }

        $this->assertSame($current->getEtag(), $this->remoteConfig->get()->getEtag());
    }

    public function testPublishInvalidTemplate()
    {
        $current = $this->remoteConfig->get();

        $template = $this->templateWithTooManyParameters();

        try {
            $this->remoteConfig->validate($template);
            $this->fail('A '.ValidationFailed::class.' should have been thrown');
        } catch (\Throwable $e) {
            $this->assertInstanceOf(ValidationFailed::class, $e);
        }

        $this->assertSame($current->getEtag(), $this->remoteConfig->get()->getEtag());
    }

    public function testListVersions()
    {
        foreach ($this->remoteConfig->listVersions() as $version) {
            $this->assertInstanceOf(RemoteConfig\Version::class, $version);
            break;
        }
    }

    public function testGetVersion()
    {
        foreach ($this->remoteConfig->listVersions() as $first) {
            break;
        }

        $same = $this->remoteConfig->getVersion($first->versionNumber());

        $this->assertTrue($first->versionNumber()->equalsTo($same->versionNumber()));
    }

    public function testFindVersionsWithLimit()
    {
        $counter = 0;

        foreach ($this->remoteConfig->listVersions(['limit' => 1]) as $version) {
            ++$counter;
        }

        $this->assertSame(1, $counter);
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
