<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Tests\UnitTestCase;

class ApnsConfigTest extends UnitTestCase
{
    public function testCreateFromValidArray()
    {
        $apnsConfig = ApnsConfig::fromArray($array = [
            'key1' => $title = 'My key1',
            'key2' => $body = 'My key2',
        ]);

        $this->assertEquals($array, $apnsConfig->jsonSerialize());
    }
}
