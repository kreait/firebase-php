<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Messaging;

use Kreait\Firebase\Exception\HasErrors;
use Kreait\Firebase\Exception\MessagingException;
use RuntimeException;

final class NotFound extends RuntimeException implements MessagingException
{
    use HasErrors;

    /**
     * @internal
     *
     * @param string[] $errors
     *
     * @return static
     */
    public function withErrors(array $errors)
    {
        $new = new self($this->getMessage(), $this->getCode(), $this->getPrevious());
        $new->errors = $errors;

        return $new;
    }
}
