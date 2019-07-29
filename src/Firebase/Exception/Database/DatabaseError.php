<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Database;

use Kreait\Firebase\Exception\DatabaseException;
use Kreait\Firebase\Exception\HasRequestAndResponse;
use RuntimeException;

final class DatabaseError extends RuntimeException implements DatabaseException
{
    use HasRequestAndResponse;
}
