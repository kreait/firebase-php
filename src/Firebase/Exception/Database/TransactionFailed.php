<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Database;

use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Exception\FirebaseException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

final class TransactionFailed extends RuntimeException implements FirebaseException
{
    /** @var Reference */
    private $reference;

    /** @var RequestInterface|null */
    private $request;

    /** @var ResponseInterface|null */
    private $response;

    public function __construct(Reference $query, $message = '', $code = 0, Throwable $previous = null)
    {
        $request = null;
        $response = null;

        if (\trim($message) === '') {
            $queryPath = $query->getPath();

            $message = "The transaction on {$queryPath} failed";

            if ($previous instanceof PreconditionFailed) {
                $message = "The reference {$queryPath} has changed remotely since the transaction has been started.";
            } elseif ($previous !== null) {
                $message = "The transaction on {$query->getPath()} failed: {$previous->getMessage()}";
            }
        }

        parent::__construct($message, $code, $previous);

        $this->reference = $query;

        if ($previous && $previous instanceof RequestException) {
            $requestException = $previous;
        } elseif ($previous && ($prePrevious = $previous->getPrevious()) && ($prePrevious instanceof RequestException)) {
            $requestException = $prePrevious;
        } else {
            $requestException = null;
        }

        if ($requestException) {
            $this->request = $requestException->getRequest();
            $this->response = $requestException->getResponse();
        }
    }

    public static function onReference(Reference $reference, Throwable $error = null): self
    {
        $code = $error ? $error->getCode() : 0;

        return new self($reference, '', $code, $error);
    }

    public function getReference(): Reference
    {
        return $this->reference;
    }

    /**
     * @deprecated 4.28.0
     *
     * @return RequestInterface|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @deprecated 4.28.0
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
