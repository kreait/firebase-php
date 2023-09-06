<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Messaging;

use Kreait\Firebase\Exception\HasErrors;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Exception\RuntimeException;

final class AuthenticationError extends RuntimeException implements MessagingException
{
    use HasErrors;

    /**
     * @internal
     *
     * @param array<non-empty-string> $errors
     */
    public function withErrors(array $errors): self
    {
        $new = new self($this->getMessage(), $this->getCode(), $this->getPrevious());
        $new->errors = $errors;

        return $new;
    }
}
