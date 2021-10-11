<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Throwable;

final class SendReport
{
    private MessageTarget $target;

    /** @var array<array-key, mixed>|null */
    private ?array $result = null;
    private ?Message $message = null;
    private ?Throwable $error = null;

    private function __construct(MessageTarget $target)
    {
        $this->target = $target;
    }

    /**
     * @param array<array-key, mixed> $response
     */
    public static function success(MessageTarget $target, array $response, ?Message $message = null): self
    {
        $report = new self($target);
        $report->result = $response;
        $report->message = $message;

        return $report;
    }

    public static function failure(MessageTarget $target, Throwable $error, ?Message $message = null): self
    {
        $report = new self($target);
        $report->error = $error;
        $report->message = $message;

        return $report;
    }

    public function target(): MessageTarget
    {
        return $this->target;
    }

    public function isSuccess(): bool
    {
        return $this->error === null;
    }

    public function isFailure(): bool
    {
        return $this->error !== null;
    }

    public function messageTargetWasInvalid(): bool
    {
        $errorMessage = $this->error !== null ? $this->error->getMessage() : '';

        return $this->messageWasInvalid() && \preg_match('/((not.+valid)|invalid).+token/i', $errorMessage) === 1;
    }

    public function messageWasInvalid(): bool
    {
        return $this->error instanceof InvalidMessage;
    }

    public function messageWasSentToUnknownToken(): bool
    {
        return $this->error instanceof NotFound;
    }

    /**
     * @return array<mixed>|null
     */
    public function result(): ?array
    {
        return $this->result;
    }

    public function error(): ?Throwable
    {
        return $this->error;
    }

    public function message(): ?Message
    {
        return $this->message;
    }
}
