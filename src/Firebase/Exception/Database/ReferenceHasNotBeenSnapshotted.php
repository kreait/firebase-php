<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Database;

use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Exception\DatabaseException;
use RuntimeException;
use Throwable;

final class ReferenceHasNotBeenSnapshotted extends RuntimeException implements DatabaseException
{
    private Reference $reference;

    public function __construct(Reference $query, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $message = \trim($message);

        if ($message === '') {
            $message = "The reference {$query->getPath()} has not been snapshotted.";
        }

        parent::__construct($message, $code, $previous);

        $this->reference = $query;
    }

    public function getReference(): Reference
    {
        return $this->reference;
    }
}
