<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class AndroidConfigTest extends UnitTestCase
{
    /**
     * @test
     */
    public function it_is_empty_when_it_is_empty(): void
    {
        $this->assertSame('[]', \json_encode(AndroidConfig::new()));
    }

    /**
     * @test
     */
    public function it_has_a_default_sound(): void
    {
        $expected = [
            'notification' => [
                'sound' => 'default',
            ],
        ];

        $this->assertJsonStringEqualsJsonString(
            \json_encode($expected),
            \json_encode(AndroidConfig::new()->withDefaultSound())
        );
    }

    /**
     * @test
     * @dataProvider validDataProvider
     */
    public function it_can_be_created_from_an_array(array $data): void
    {
        $config = AndroidConfig::fromArray($data);

        $this->assertEquals($data, $config->jsonSerialize());
    }

    public function validDataProvider()
    {
        return [
            'full_config' => [[
                // https://firebase.google.com/docs/cloud-messaging/admin/send-messages#android_specific_fields
                'ttl' => '3600s',
                'priority' => 'normal',
                'notification' => [
                    'title' => '$GOOG up 1.43% on the day',
                    'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                    'icon' => 'stock_ticker_update',
                    'color' => '#f45342',
                ],
            ]],
        ];
    }
}
