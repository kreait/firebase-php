<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Database;

use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Exception\FirebaseException;
use RuntimeException;

final class ReferenceHasNotBeenSnapshotted extends RuntimeException implements FirebaseException
{
    /** @var Reference|null */
    private $reference;

    public static function with(Reference $reference): self
    {
        $message = 'Before updating or deleting a reference, you must snapshot it.';
        $message .= ' See https://firebase-php.readthedocs.io/en/latest/realtime-database.html#database-transactions';
        $message .= ' for more information.';

        $error = new self($message);
        $error->reference = $reference;

        return $error;
    }

    /**
     * @return Reference|null
     */
    public function getReference()
    {
        return $this->reference;
    }
}
