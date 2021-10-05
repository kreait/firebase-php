<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class AndroidConfigTest extends UnitTestCase
{
    public function testItIsEmptyWhenItIsEmpty(): void
    {
        $this->assertSame('[]', \json_encode(AndroidConfig::new()));
    }

    public function testItHasADefaultSound(): void
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

    public function testItCanHaveAPriority(): void
    {
        $config = AndroidConfig::new()->withNormalPriority();
        $this->assertSame('normal', $config->jsonSerialize()['priority']);

        $config = AndroidConfig::new()->withHighPriority();
        $this->assertSame('high', $config->jsonSerialize()['priority']);
    }

    /**
     * @dataProvider validDataProvider
     *
     * @param array<string, array<string, mixed>> $data
     */
    public function testItCanBeCreatedFromAnArray(array $data): void
    {
        $config = AndroidConfig::fromArray($data);

        $this->assertEquals($data, $config->jsonSerialize());
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function validDataProvider(): array
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
                    'sound' => 'default',
                ],
            ]],
        ];
    }
}
