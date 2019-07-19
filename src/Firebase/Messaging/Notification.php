<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

class Notification implements \JsonSerializable
{
    /** @var string|null */
    private $title;

    /** @var string|null */
    private $body;

    /** @var string|null */
    private $imageUrl;

    private function __construct(string $title = null, string $body = null, string $imageUrl = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->imageUrl = $imageUrl;
    }

    public static function create(string $title = null, string $body = null, string $imageUrl = null): self
    {
        return new self($title, $body, $imageUrl);
    }

    public static function fromArray(array $data): self
    {
        try {
            return new self(
                $data['title'] ?? null,
                $data['body'] ?? null,
                $data['image'] ?? null
            );
        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
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

    /**
     * @return string|null
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * @return string|null
     */
    public function imageUrl()
    {
        return $this->imageUrl;
    }

    public function jsonSerialize()
    {
        return \array_filter([
            'title' => $this->title,
            'body' => $this->body,
            'image' => $this->imageUrl,
        ], static function ($value) {
            return $value !== null;
        });
    }
}
