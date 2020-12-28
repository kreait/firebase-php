<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class DeleteUserError
{
    /** @var int */
    private $index;

    /** @var string */
    private $localId;

    /** @var string */
    private $message;

    private function __construct(int $index, string $localId, string $message)
    {
        $this->index = $index;
        $this->localId = $localId;
        $this->message = $message;
    }

    /**
     * @param array<string, mixed> $error
     */
    public static function fromResponseData(array $error): self
    {
        return new self($error['index'], $error['localId'], $error['message']);
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getLocalId(): string
    {
        return $this->localId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
