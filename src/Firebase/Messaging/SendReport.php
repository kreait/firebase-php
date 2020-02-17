<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Throwable;

class SendReport
{
    /** @var MessageTarget */
    private $target;

    /** @var array|null */
    private $result;

    /** @var Throwable|null */
    private $error;

    private function __construct()
    {
    }

    public static function success(MessageTarget $target, $response): self
    {
        $report = new self();
        $report->target = $target;
        $report->result = $response;

        return $report;
    }

    public static function failure(MessageTarget $target, Throwable $error): self
    {
        $report = new self();
        $report->target = $target;
        $report->error = $error;

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
        $errorMessage = $this->error ? $this->error->getMessage() : '';

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
     * @return array|null
     */
    public function result()
    {
        return $this->result;
    }

    /**
     * @return Throwable|null
     */
    public function error()
    {
        return $this->error;
    }
}
