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

    public function __construct(string $title = null, string $body = null)
    {
        $this->title = $title;
        $this->body = $body;
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

    public function jsonSerialize()
    {
        return array_filter([
            'title' => $this->title,
            'body' => $this->body,
        ]);
    }
}
