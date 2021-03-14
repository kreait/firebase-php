<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class ApnsConfigTest extends UnitTestCase
{
    /**
     * @test
     */
    public function it_is_empty_when_it_is_empty(): void
    {
        $this->assertSame('[]', \json_encode(ApnsConfig::new()));
    }

    /**
     * @test
     */
    public function it_has_a_default_sound(): void
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

    /**
     * @test
     */
    public function it_has_a_badge(): void
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
     * @test
     * @dataProvider validDataProvider
     */
    public function it_can_be_created_from_an_array(array $data): void
    {
        $config = ApnsConfig::fromArray($data);

        $this->assertEquals($data, $config->jsonSerialize());
    }

    /**
     * @test
     */
    public function it_can_have_a_priority(): void
    {
        $config = ApnsConfig::new()->withImmediatePriority();
        $this->assertSame('10', $config->jsonSerialize()['headers']['apns-priority']);

        $config = ApnsConfig::new()->withPowerConservingPriority();
        $this->assertSame('5', $config->jsonSerialize()['headers']['apns-priority']);
    }

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
