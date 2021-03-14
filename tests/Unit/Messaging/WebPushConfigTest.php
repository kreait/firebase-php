<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class WebPushConfigTest extends UnitTestCase
{
    /**
     * @dataProvider validDataProvider
     */
    public function testCreateFromArray(array $data): void
    {
        $config = WebPushConfig::fromArray($data);

        $this->assertEquals($data, $config->jsonSerialize());
    }

    /**
     * @test
     */
    public function it_can_have_a_priority(): void
    {
        $config = WebPushConfig::new()->withVeryLowUrgency();
        $this->assertSame('very-low', $config->jsonSerialize()['headers']['Urgency']);

        $config = WebPushConfig::new()->withLowUrgency();
        $this->assertSame('low', $config->jsonSerialize()['headers']['Urgency']);

        $config = WebPushConfig::new()->withNormalUrgency();
        $this->assertSame('normal', $config->jsonSerialize()['headers']['Urgency']);

        $config = WebPushConfig::new()->withHighUrgency();
        $this->assertSame('high', $config->jsonSerialize()['headers']['Urgency']);
    }

    public function validDataProvider()
    {
        return [
            'full_config' => [
                // https://firebase.google.com/docs/cloud-messaging/admin/send-messages#webpush_specific_fields
                'headers' => [
                    'Urgency' => 'normal',
                ],
                'notification' => [
                    'title' => '$GOOG up 1.43% on the day',
                    'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                    'icon' => 'https://my-server/icon.png',
                ],
            ],
        ];
    }
}
