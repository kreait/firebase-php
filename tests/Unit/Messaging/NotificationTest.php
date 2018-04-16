<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Tests\UnitTestCase;

class NotificationTest extends UnitTestCase
{
    public function testCreateEmptyNotification()
    {
        $notification = Notification::create();

        $this->assertNull($notification->title());
        $this->assertNull($notification->body());
        $this->assertEmpty($notification->jsonSerialize());
    }

    public function testCreateWithEmptyStrings()
    {
        $notification = Notification::create('', '');
        $this->assertSame('', $notification->title());
        $this->assertSame('', $notification->body());
        $this->assertEquals(['title' => '', 'body' => ''], $notification->jsonSerialize());
    }

    public function testCreateWithValidFields()
    {
        $notification = Notification::create()
            ->withTitle($title = 'My Title')
            ->withBody($body = 'My Body');

        $this->assertSame($title, $notification->title());
        $this->assertSame($body, $notification->body());
    }

    public function testCreateFromValidArray()
    {
        $notification = Notification::fromArray($array = [
            'title' => $title = 'My Title',
            'body' => $body = 'My Body',
        ]);

        $this->assertSame($title, $notification->title());
        $this->assertSame($body, $notification->body());
        $this->assertEquals($array, $notification->jsonSerialize());
    }

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
