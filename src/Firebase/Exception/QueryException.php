<?php

namespace Kreait\Firebase\Exception;

use Kreait\Firebase\Database\Query;

class QueryException extends \RuntimeException implements FirebaseException
{
    private $query;

    public function __construct(Query $query, $message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->query = $query;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    public static function fromApiException(ApiException $e, Query $query)
    {
        $message = $e->getMessage();

        if (stripos($message, 'index not defined') !== false) {
            throw new IndexNotDefined($query, $message, $e->getCode(), $e);
        }

        if (stripos($message, 'orderby must be defined') !== false) {
            $message = 'An order must be defined for a query when applying filters.'
                .' You can use Query::orderByValue(), Query::orderByKey() or Query::orderByChild().';
        } elseif (stripos($message, 'key index passed non') !== false) {
            $message = 'The query is ordered by key, but you tried to filter it with a non-string value.';
        }

        return new self($query, $message, $e->getCode(), $e);
    }
}
