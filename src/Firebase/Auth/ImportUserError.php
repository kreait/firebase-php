<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

final class ImportUserError
{
    /** @var int */
    private $index;

    /** @var string */
    private $message;

    private function __construct(int $index, string $message)
    {
        $this->index = $index;
        $this->message = $message;
    }

    /**
     * @param array<string, mixed> $error
     */
    public static function fromResponseData(array $error): self
    {
        return new self($error['index'], $error['message']);
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
