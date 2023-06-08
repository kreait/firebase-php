<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Beste\Json;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class ApnsConfigTest extends UnitTestCase
{
    #[Test]
    public function itIsEmptyWhenItIsEmpty(): void
    {
        $this->assertSame('[]', Json::encode(ApnsConfig::new()));
    }

    #[Test]
    public function itHasADefaultSound(): void
    {
        $expected = [
            'payload' => [
                'aps' => [
                    'sound' => 'default',
                ],
            ],
        ];

        $this->assertJsonStringEqualsJsonString(
            Json::encode($expected),
            Json::encode(ApnsConfig::new()->withDefaultSound()),
        );
    }

    #[Test]
    public function itHasABadge(): void
    {
        $expected = [
            'payload' => [
                'aps' => [
                    'badge' => 123,
                ],
            ],
        ];

        $this->assertJsonStringEqualsJsonString(
            Json::encode($expected),
            Json::encode(ApnsConfig::new()->withBadge(123)),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('validDataProvider')]
    #[Test]
    public function itCanBeCreatedFromAnArray(array $data): void
    {
        $config = ApnsConfig::fromArray($data);

        $this->assertEqualsCanonicalizing($data, $config->jsonSerialize());
    }

    #[Test]
    public function itCanHaveAPriority(): void
    {
        $config = ApnsConfig::new()->withImmediatePriority();
        $this->assertSame('10', $config->jsonSerialize()['headers']['apns-priority']);

        $config = ApnsConfig::new()->withPowerConservingPriority();
        $this->assertSame('5', $config->jsonSerialize()['headers']['apns-priority']);
    }

    #[Test]
    public function itHasASubtitle(): void
    {
        $expected = [
            'payload' => [
                'aps' => [
                    'subtitle' => 'i am a subtitle',
                ],
            ],
        ];

        $this->assertJsonStringEqualsJsonString(
            Json::encode($expected),
            Json::encode(ApnsConfig::new()->withSubtitle('i am a subtitle')),
        );
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function validDataProvider()
    {
        return [
            'full_config' => [[
                // https://firebase.google.com/docs/cloud-messaging/admin/send-messages#apns_specific_fields
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => '$GOOGLE up 1.43% on the day',
                            'body' => '$GOOGLE gained 11.80 points to close at 835.67, up 1.43% on the day.',
                        ],
                        'badge' => 42,
                        'sound' => 'default',
                    ],
                ],
            ]],
        ];
    }
}
