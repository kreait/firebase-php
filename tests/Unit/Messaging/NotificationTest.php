<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class NotificationTest extends UnitTestCase
{
    #[Test]
    public function createWithEmptyStrings(): void
    {
        $notification = Notification::create('', '', '');
        $this->assertSame('', $notification->title());
        $this->assertSame('', $notification->body());
        $this->assertSame('', $notification->imageUrl());
        $this->assertEqualsCanonicalizing(['title' => '', 'body' => '', 'image' => ''], $notification->jsonSerialize());
    }

    #[Test]
    public function createWithValidFields(): void
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

    #[Test]
    public function createFromValidArray(): void
    {
        $notification = Notification::fromArray($array = [
            'title' => $title = 'My Title',
            'body' => $body = 'My Body',
            'image' => $imageUrl = 'https://domain.tld/image.ext',
        ]);

        $this->assertSame($title, $notification->title());
        $this->assertSame($body, $notification->body());
        $this->assertSame($imageUrl, $notification->imageUrl());
        $this->assertEqualsCanonicalizing($array, $notification->jsonSerialize());
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
