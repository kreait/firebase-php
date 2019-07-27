<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use Kreait\Firebase\Database\Query;

/**
 * @deprecated 4.28.0 catch specific exceptions or \Kreait\Firebase\Exception\DatabaseException instead
 */
interface QueryException extends DatabaseException
{
    public function getQuery(): Query;
}
