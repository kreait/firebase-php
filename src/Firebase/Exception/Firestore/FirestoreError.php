<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Firestore;

use Kreait\Firebase\Exception\FirestoreException;
use RuntimeException;

final class FirestoreError extends RuntimeException implements FirestoreException
{
}
