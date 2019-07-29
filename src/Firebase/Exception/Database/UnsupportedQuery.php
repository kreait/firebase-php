<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Database;

use Kreait\Firebase\Database\Query;
use Kreait\Firebase\Exception\DatabaseException;
use Kreait\Firebase\Exception\HasRequestAndResponse;
use Kreait\Firebase\Exception\IndexNotDefined;
use RuntimeException;
use Throwable;

final class UnsupportedQuery extends RuntimeException implements DatabaseException, IndexNotDefined
{
    use HasRequestAndResponse;

    /** @var Query */
    private $query;

    public function __construct(Query $query, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->query = $query;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }
}
