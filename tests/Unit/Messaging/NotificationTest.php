<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Tests\UnitTestCase;

class NotificationTest extends UnitTestCase
{
    /**
     * @param array $data
     * @dataProvider invalidDataProvider
     */
    public function testCreateWithInvalidData(array $data)
    {
        $this->expectException(InvalidArgumentException::class);
        Notification::fromArray($data);
    }

    public function invalidDataProvider(): array
    {
        return [
            'non_string_title' => [['title' => 1]],
            'non_string_body' => [['body' => 1]],
        ];
    }
}
