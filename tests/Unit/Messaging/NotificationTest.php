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
    public function testCreateWithEmptyStrings(): void
    {
        $notification = Notification::create('', '', '');
        $this->assertSame('', $notification->title());
        $this->assertSame('', $notification->body());
        $this->assertSame('', $notification->imageUrl());
        $this->assertEquals(['title' => '', 'body' => '', 'image' => ''], $notification->jsonSerialize());
    }

    public function testCreateWithValidFields(): void
    {
        $notification = Notification::create('title', 'body')
            ->withTitle($title = 'My Title')
            ->withBody($body = 'My Body')
            ->withImageUrl($imageUrl = 'https://domain.tld/image.ext')
        ;

        $this->assertSame($title, $notification->title());
        $this->assertSame($body, $notification->body());
        $this->assertSame($imageUrl, $notification->imageUrl());
    }

    public function testCreateFromValidArray(): void
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
     *
     * @param array<string, mixed> $data
     */
    public function testCreateWithInvalidData(array $data): void
    {
        $this->expectException(InvalidArgumentException::class);
        Notification::fromArray($data);
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function invalidDataProvider(): array
    {
        return [
            'empty_title_and_body' => [['title' => null, 'body' => null]],
        ];
    }
}
