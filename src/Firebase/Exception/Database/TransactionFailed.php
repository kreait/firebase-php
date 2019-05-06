<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Database;

use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Exception\FirebaseException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class TransactionFailed extends RuntimeException implements FirebaseException
{
    /** @var Reference */
    private $reference;

    /** @var RequestInterface */
    private $request;

    /** @var ResponseInterface|null */
    private $response;

    public static function forReferenceAndApiException(Reference $reference, ApiException $previous)
    {
        $message = 'The reference has changed remotely since the transaction has been started.';

        $error = new self($message, $previous->getCode(), $previous);
        $error->reference = $reference;
        $error->request = $previous->getRequest();
        $error->response = $previous->getResponse();

        return $error;
    }

    public function getReference(): Reference
    {
        return $this->reference;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
