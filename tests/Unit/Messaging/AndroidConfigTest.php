<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Tests\UnitTestCase;

class AndroidConfigTest extends UnitTestCase
{
    public function testCreateFromValidArray()
    {
        $androidConfig = AndroidConfig::fromArray($array = [
            'key1' => $title = 'My key1',
            'key2' => $body = 'My key2',
        ]);

        $this->assertEquals($array, $androidConfig->jsonSerialize());
    }
}
