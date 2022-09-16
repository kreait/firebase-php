<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class NotificationTest extends UnitTestCase
{
    public function testCreateWithEmptyStrings(): void
    {
        $notification = Notification::create('', '', '');
        self::assertSame('', $notification->title());
        self::assertSame('', $notification->body());
        self::assertSame('', $notification->imageUrl());
        self::assertEquals(['title' => '', 'body' => '', 'image' => ''], $notification->jsonSerialize());
    }

    public function testCreateWithValidFields(): void
    {
        $notification = Notification::create('title', 'body')
            ->withTitle($title = 'My Title')
            ->withBody($body = 'My Body')
            ->withImageUrl($imageUrl = 'https://domain.tld/image.ext');

        self::assertSame($title, $notification->title());
        self::assertSame($body, $notification->body());
        self::assertSame($imageUrl, $notification->imageUrl());
    }

    public function testCreateFromValidArray(): void
    {
        $notification = Notification::fromArray($array = [
            'title' => $title = 'My Title',
            'body' => $body = 'My Body',
            'image' => $imageUrl = 'https://domain.tld/image.ext',
        ]);

        self::assertSame($title, $notification->title());
        self::assertSame($body, $notification->body());
        self::assertSame($imageUrl, $notification->imageUrl());
        self::assertEquals($array, $notification->jsonSerialize());
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
