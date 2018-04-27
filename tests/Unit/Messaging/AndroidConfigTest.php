<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Tests\UnitTestCase;

class AndroidConfigTest extends UnitTestCase
{
    /**
     * @dataProvider validDataProvider
     */
    public function testCreateFromArray(array $data)
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
