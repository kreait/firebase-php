<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class ApnsConfigTest extends UnitTestCase
{
    public function testItIsEmptyWhenItIsEmpty(): void
    {
        $this->assertSame('[]', \json_encode(ApnsConfig::new()));
    }

    public function testItHasADefaultSound(): void
    {
        $expected = [
            'payload' => [
                'aps' => [
                    'sound' => 'default',
                ],
            ],
        ];

        $this->assertJsonStringEqualsJsonString(
            \json_encode($expected),
            \json_encode(ApnsConfig::new()->withDefaultSound())
        );
    }

    public function testItHasABadge(): void
    {
        $expected = [
            'payload' => [
                'aps' => [
                    'badge' => 123,
                ],
            ],
        ];

        $this->assertJsonStringEqualsJsonString(
            \json_encode($expected),
            \json_encode(ApnsConfig::new()->withBadge(123))
        );
    }

    /**
     * @dataProvider validDataProvider
     *
     * @param array<string, mixed> $data
     */
    public function testItCanBeCreatedFromAnArray(array $data): void
    {
        $config = ApnsConfig::fromArray($data);

        $this->assertEquals($data, $config->jsonSerialize());
    }

    public function testItCanHaveAPriority(): void
    {
        $config = ApnsConfig::new()->withImmediatePriority();
        $this->assertSame('10', $config->jsonSerialize()['headers']['apns-priority']);

        $config = ApnsConfig::new()->withPowerConservingPriority();
        $this->assertSame('5', $config->jsonSerialize()['headers']['apns-priority']);
    }

    public function testItHasASubtitle(): void
    {
        $expected = [
            'payload' => [
                'aps' => [
                    'subtitle' => 'i am a subtitle',
                ],
            ],
        ];

        $this->assertJsonStringEqualsJsonString(
            \json_encode($expected),
            \json_encode(ApnsConfig::new()->withSubtitle('i am a subtitle'))
        );
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function validDataProvider()
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
                            'title' => '$GOOG up 1.43% on the day',
                            'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                        ],
                        'badge' => 42,
                        'sound' => 'default',
                    ],
                ],
            ]],
        ];
    }
}
