<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;
use Kreait\Firebase\Exception\InvalidArgumentException;

use function array_filter;

/**
 * @phpstan-type NotificationShape array{
 *     title?: string,
 *     body?: string,
 *     imageUrl?: string
 * }
 */
final class Notification implements JsonSerializable
{
    private ?string $title;
    private ?string $body;
    private ?string $imageUrl;
    private ?string $sound;

    /**
     * @throws InvalidArgumentException if both title and body are null
     */
    private function __construct(?string $title = null, ?string $body = null, ?string $imageUrl = null, ?string $sound = 'default')
    {
        $this->title = $title;
        $this->body = $body;
        $this->imageUrl = $imageUrl;
        $this->sound = $sound;
    }

    /**
     * @throws InvalidArgumentException if both title and body are null
     */
    public static function create(?string $title = null, ?string $body = null, ?string $imageUrl = null): self
    {
        return new self($title, $body, $imageUrl, 'default');
    }

    /**
     * @param array{
     *     title?: string,
     *     body?: string,
     *     image?: string
     *     sound?: string
     * } $data
     *
     * @throws InvalidArgumentException if both title and body are null
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['title'] ?? null,
            $data['body'] ?? null,
            $data['image'] ?? null,
            $data['sound'] ?? 'default'
        );
    }

    public function withTitle(string $title): self
    {
        $notification = clone $this;
        $notification->title = $title;

        return $notification;
    }

    public function withBody(string $body): self
    {
        $notification = clone $this;
        $notification->body = $body;

        return $notification;
    }

    public function withImageUrl(string $imageUrl): self
    {
        $notification = clone $this;
        $notification->imageUrl = $imageUrl;

        return $notification;
    }

    public function withSound(string $sound): self
    {
        $notification = clone $this;
        $notification->sound = $sound;

        return $notification;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function body(): ?string
    {
        return $this->body;
    }

    public function imageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function sound(): ?string
    {
        return $this->sound;
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'title' => $this->title,
            'body' => $this->body,
            'image' => $this->imageUrl,
            'sound' => $this->sound,
        ], static fn($value) => $value !== null);
    }
}
