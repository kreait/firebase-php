<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

final class InvalidToken extends \RuntimeException implements FirebaseException
{
    public static function because(string $reason, int $code = null, \Throwable $previous = null): self
    {
        $code = $code ?: 0;

        return new self($reason, $code, $previous);
    }
}
