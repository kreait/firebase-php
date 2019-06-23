<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

class Notification implements \JsonSerializable
{
    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $body;

    private function __construct(string $title = null, string $body = null)
    {
        $this->title = $title;
        $this->body = $body;
    }

    public static function create(string $title = null, string $body = null): self
    {
        return new self($title, $body);
    }

    public static function fromArray(array $data): self
    {
        try {
            return new self(
                $data['title'] ?? null,
                $data['body'] ?? null
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

    public function jsonSerialize()
    {
        return \array_filter([
            'title' => $this->title,
            'body' => $this->body,
        ], static function ($value) {
            return $value !== null;
        });
    }
}
