<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class NotificationTest extends UnitTestCase
{
    public function testCreateEmptyNotification()
    {
        $notification = Notification::create();

        $this->assertNull($notification->title());
        $this->assertNull($notification->body());
        $this->assertNull($notification->imageUrl());
        $this->assertEmpty($notification->jsonSerialize());
    }

    public function testCreateWithEmptyStrings()
    {
        $notification = Notification::create('', '', '');
        $this->assertSame('', $notification->title());
        $this->assertSame('', $notification->body());
        $this->assertSame('', $notification->imageUrl());
        $this->assertEquals(['title' => '', 'body' => '', 'image' => ''], $notification->jsonSerialize());
    }

    public function testCreateWithValidFields()
    {
        $notification = Notification::create()
            ->withTitle($title = 'My Title')
            ->withBody($body = 'My Body')
            ->withImageUrl($imageUrl = 'https://domain.tld/image.ext');

        $this->assertSame($title, $notification->title());
        $this->assertSame($body, $notification->body());
        $this->assertSame($imageUrl, $notification->imageUrl());
    }

    public function testCreateFromValidArray()
    {
        $notification = Notification::fromArray($array = [
            'title' => $title = 'My Title',
            'body' => $body = 'My Body',
            'image' => $imageUrl = 'https://domain.tld/image.ext',
        ]);

        $this->assertSame($title, $notification->title());
        $this->assertSame($body, $notification->body());
        $this->assertSame($imageUrl, $notification->imageUrl());
        $this->assertEquals($array, $notification->jsonSerialize());
    }

    /**
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
            'non_string_image_url' => [['image' => 1]],
        ];
    }
}
