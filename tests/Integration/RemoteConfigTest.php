<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Beste\Json;
use Kreait\Firebase\Contract\RemoteConfig;
use Kreait\Firebase\Exception\RemoteConfig\ValidationFailed;
use Kreait\Firebase\Exception\RemoteConfig\VersionMismatch;
use Kreait\Firebase\Exception\RemoteConfig\VersionNotFound;
use Kreait\Firebase\RemoteConfig\FindVersions;
use Kreait\Firebase\RemoteConfig\Parameter;
use Kreait\Firebase\RemoteConfig\ParameterValueType;
use Kreait\Firebase\RemoteConfig\Template;
use Kreait\Firebase\RemoteConfig\UpdateOrigin;
use Kreait\Firebase\RemoteConfig\UpdateType;
use Kreait\Firebase\RemoteConfig\Version;
use Kreait\Firebase\RemoteConfig\VersionNumber;
use Kreait\Firebase\Tests\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;
use Throwable;

/**
 * @internal
 */
final class RemoteConfigTest extends IntegrationTestCase
{
    /**
     * @var string
     */
    private const TEMPLATE_CONFIG = <<<'CONFIG'
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
                },
                {
                    "name": "user_exists",
                    "expression": "true",
                    "tagColor": "TEAL"
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
                },
                "no_value_type": {
                    "defaultValue": "1"
                },
                "unspecified_value_type": {
                    "defaultValue": "1",
                    "valueType": "PARAMETER_VALUE_TYPE_UNSPECIFIED"
                },
                "string_value_type": {
                    "defaultValue": "1",
                    "valueType": "STRING"
                },
                "numeric_value_type": {
                    "defaultValue": "1",
                    "valueType": "NUMBER"
                },
                "boolean_value_type": {
                    "defaultValue": "true",
                    "valueType": "BOOLEAN"
                },
                "json_value_type": {
                    "defaultValue": "{\"key\": \"value\"}",
                    "valueType": "JSON"
                },
                "is_ready_for_rollout": {
                    "defaultValue": "false",
                    "valueType": "BOOLEAN",
                    "conditionalValues": {
                        "lang_german": {
                            "value": "false"
                        },
                        "user_exists": {
                            "rollout_id": "rollout_2",
                            "value": "true",
                            "percent": 50
                        }
                    }
                }
            },
            "parameterGroups": {
                "welcome_messages": {
                    "description": "A group of parameters",
                    "parameters": {
                        "welcome_message_new_users": {
                            "defaultValue": {
                                "value": "Welcome, new user!"
                            },
                            "conditionalValues": {
                                "lang_german": {
                                    "value": "Willkommen, neuer Benutzer!"
                                },
                                "lang_french": {
                                    "value": "Bienvenu, nouvel utilisateur!"
                                }
                            },
                            "description": "This is a welcome message for new users"
                        },
                        "welcome_message_existing_users": {
                            "defaultValue": {
                                "value": "Welcome, existing user!"
                            },
                            "conditionalValues": {
                                "lang_german": {
                                    "value": "Willkommen, bestehender Benutzer!"
                                },
                                "lang_french": {
                                    "value": "Bienvenu, utilisant existant!"
                                }
                            },
                            "description": "This is a welcome message for existing users"
                        }
                    }
                }
            }
        }
        CONFIG;
    private Template $template;
    private RemoteConfig $remoteConfig;

    protected function setUp(): void
    {
        $this->remoteConfig = self::$factory->createRemoteConfig();
        $this->template = Template::fromArray(Json::decode(self::TEMPLATE_CONFIG, true));
    }

    #[Test]
    public function forcePublishAndGet(): void
    {
        $this->remoteConfig->publish($this->template);

        $check = $this->remoteConfig->get();

        $parameters = $check->parameters();
        $this->assertSameSize($this->template->parameters(), $parameters);
        $this->assertSame(ParameterValueType::STRING, $parameters['no_value_type']->valueType());
        $this->assertSame(ParameterValueType::STRING, $parameters['unspecified_value_type']->valueType());
        $this->assertSame(ParameterValueType::STRING, $parameters['string_value_type']->valueType());
        $this->assertSame(ParameterValueType::NUMBER, $parameters['numeric_value_type']->valueType());
        $this->assertSame(ParameterValueType::BOOL, $parameters['boolean_value_type']->valueType());
        $this->assertSame(ParameterValueType::JSON, $parameters['json_value_type']->valueType());

        $this->assertSameSize($this->template->conditions(), $check->conditions());
        $this->assertSameSize($this->template->conditionNames(), $check->conditionNames());

        $version = $check->version();

        $this->assertSame(UpdateType::FORCED_UPDATE, (string) $version?->updateType());
        $this->assertSame(UpdateOrigin::REST_API, (string) $version?->updateOrigin());
    }

    #[Test]
    public function getTemplateWithVersion(): void
    {
        $template = $this->remoteConfig->get();
        $version = $template->version();
        assert($version !== null);

        $check = $this->remoteConfig->get($version);

        $this->assertTrue($version->versionNumber()->equalsTo($check->version()?->versionNumber()));
    }

    #[Test]
    public function getTemplateWithVersionNumber(): void
    {
        $template = $this->remoteConfig->get();
        $version = $template->version();
        assert($version !== null);

        $check = $this->remoteConfig->get($version->versionNumber());

        $this->assertTrue($version->versionNumber()->equalsTo($check->version()?->versionNumber()));
    }

    #[Test]
    public function getTemplateWithVersionNumberString(): void
    {
        $template = $this->remoteConfig->get();
        $version = $template->version();
        assert($version !== null);

        $check = $this->remoteConfig->get((string) $version->versionNumber());

        $this->assertTrue($version->versionNumber()->equalsTo($check->version()?->versionNumber()));
    }

    #[Test]
    public function publishOutdatedConfig(): void
    {
        $this->remoteConfig->publish($this->template);

        $published = $this->remoteConfig->get();

        $this->remoteConfig->publish($published);

        // The published template has now a different etag, so publishing it again
        // with our old etag value should fail
        $this->expectException(VersionMismatch::class);
        $this->remoteConfig->publish($published);
    }

    #[Test]
    public function validateValidTemplate(): void
    {
        $this->remoteConfig->validate($this->template);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function validateInvalidTemplate(): void
    {
        $template = $this->templateWithTooManyParameters();

        $this->expectException(ValidationFailed::class);
        $this->remoteConfig->validate($template);
    }

    #[Test]
    public function publishInvalidTemplate(): void
    {
        $version = $this->remoteConfig->get()->version();

        if (!$version instanceof Version) {
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

        if (!$refetchedVersion instanceof Version) {
            $this->fail('The template has no version');
        }

        $this->assertTrue(
            $currentVersionNumber->equalsTo($refetchedVersion->versionNumber()),
            "Expected the template version to be {$currentVersionNumber}, got {$refetchedVersion->versionNumber()}",
        );
    }

    #[Test]
    public function rollback(): void
    {
        $initialVersion = $this->remoteConfig->get()->version();

        if (!$initialVersion instanceof Version) {
            $this->fail('The template has no version');
        }

        $initialVersionNumber = $initialVersion->versionNumber();

        $query = FindVersions::all()
            ->withLimit(2)
            ->upToVersion($initialVersionNumber)
        ;

        $targetVersionNumber = null;

        foreach ($this->remoteConfig->listVersions($query) as $version) {
            $versionNumber = $version->versionNumber();

            if (!$versionNumber->equalsTo($initialVersionNumber)) {
                $targetVersionNumber = $versionNumber;
            }
        }

        if ($targetVersionNumber === null) {
            $this->fail('A previous version number should have been retrieved');
        }

        $this->remoteConfig->rollbackToVersion($targetVersionNumber);

        $newVersion = $this->remoteConfig->get()->version();

        if (!$newVersion instanceof Version) {
            $this->fail('The new template has no version');
        }

        $newVersionNumber = $newVersion->versionNumber();
        $rollbackSource = $newVersion->rollbackSource();

        if (!$rollbackSource instanceof VersionNumber) {
            $this->fail('The new template version has no rollback source');
        }

        $this->assertFalse($newVersionNumber->equalsTo($initialVersionNumber));
        $this->assertTrue($rollbackSource->equalsTo($targetVersionNumber));
    }

    #[Test]
    public function listVersionsWithoutFilters(): void
    {
        $count = 0;
        // We only need to know that the first returned value is a version,
        // no need to iterate through all of them
        foreach ($this->remoteConfig->listVersions() as $version) {
            ++$count;
            break;
        }

        $this->assertSame(1, $count);
    }

    #[Test]
    public function findVersionsWithFilters(): void
    {
        $currentVersion = $this->remoteConfig->get()->version();

        if (!$currentVersion instanceof Version) {
            $this->fail('The new template has no version');
        }

        $currentVersionUpdateDate = $currentVersion->updatedAt();

        $query = [
            'startingAt' => $currentVersionUpdateDate->modify('-2 months'),
            'endingAt' => $currentVersionUpdateDate,
            'lastVersionBeing' => $currentVersion->versionNumber(),
            'pageSize' => 1,
            'limit' => $limit = 2,
        ];

        $counter = 0;

        foreach ($this->remoteConfig->listVersions($query) as $version) {
            ++$counter;

            // Protect us from an infinite loop
            if ($counter > $limit) {
                $this->fail('The query returned more values than expected');
            }
        }

        $this->assertLessThanOrEqual($limit, $counter);
    }

    #[Test]
    public function getVersion(): void
    {
        $currentVersion = $this->remoteConfig->get()->version();

        if (!$currentVersion instanceof Version) {
            $this->fail('The template has no version');
        }

        $currentVersionNumber = $currentVersion->versionNumber();

        $check = $this->remoteConfig->getVersion($currentVersionNumber);

        $this->assertTrue($check->versionNumber()->equalsTo($currentVersionNumber));
    }

    #[Test]
    public function getNonExistingVersion(): void
    {
        $currentVersion = $this->remoteConfig->get()->version();

        if (!$currentVersion instanceof Version) {
            $this->fail('The template has no version');
        }

        $nextButNonExisting = (int) (string) $currentVersion->versionNumber() + 100;

        $this->expectException(VersionNotFound::class);
        $this->remoteConfig->getVersion($nextButNonExisting);
    }

    #[Test]
    public function validateEmptyTemplate(): void
    {
        $this->remoteConfig->validate(Template::new());
        $this->addToAssertionCount(1);
    }

    private function templateWithTooManyParameters(): Template
    {
        $template = Template::new();

        for ($i = 0; $i < 3001; ++$i) {
            $template = $template->withParameter(Parameter::named('i_'.$i, 'v_'.$i));
        }

        return $template;
    }
}
