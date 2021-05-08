<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Messaging;

use DateTimeImmutable;
use Kreait\Firebase\Exception\HasErrors;
use Kreait\Firebase\Exception\MessagingException;
use RuntimeException;

final class ServerUnavailable extends RuntimeException implements MessagingException
{
    use HasErrors;

    private ?DateTimeImmutable $retryAfter = null;

    /**
     * @internal
     *
     * @param string[] $errors
     */
    public function withErrors(array $errors): self
    {
        $new = new self($this->getMessage(), $this->getCode(), $this->getPrevious());
        $new->errors = $errors;
        $new->retryAfter = $this->retryAfter;

        return $new;
    }

    public function withRetryAfter(DateTimeImmutable $retryAfter): self
    {
        $new = new self($this->getMessage(), $this->getCode(), $this->getPrevious());
        $new->errors = $this->errors;
        $new->retryAfter = $retryAfter;

        return $new;
    }

    public function retryAfter(): ?DateTimeImmutable
    {
        return $this->retryAfter;
    }
}
