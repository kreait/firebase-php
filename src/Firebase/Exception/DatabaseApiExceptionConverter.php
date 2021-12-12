<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\Database\ApiConnectionFailed;
use Kreait\Firebase\Exception\Database\DatabaseError;
use Kreait\Firebase\Http\ErrorResponseParser;
use Psr\Http\Client\NetworkExceptionInterface;
use Throwable;

/**
 * @internal
 */
class DatabaseApiExceptionConverter
{
    private ErrorResponseParser $responseParser;

    public function __construct()
    {
        $this->responseParser = new ErrorResponseParser();
    }

    public function convertException(Throwable $exception): DatabaseException
    {
        if ($exception instanceof RequestException) {
            return $this->convertGuzzleRequestException($exception);
        }

        if ($exception instanceof NetworkExceptionInterface) {
            return new ApiConnectionFailed('Unable to connect to the API: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        return new DatabaseError($exception->getMessage(), $exception->getCode(), $exception);
    }

    private function convertGuzzleRequestException(RequestException $e): DatabaseException
    {
        $message = $e->getMessage();
        $code = $e->getCode();
        $response = $e->getResponse();

        if ($response !== null) {
            $message = $this->responseParser->getErrorReasonFromResponse($response);
            $code = $response->getStatusCode();
        }

        switch ($code) {
            case 401:
            case 403:
                return new Database\PermissionDenied($message, $code, $e);
            case 412:
                return new Database\PreconditionFailed($message, $code, $e);
            case 404:
                return Database\DatabaseNotFound::fromUri($e->getRequest()->getUri());
        }

        return new DatabaseError($message, $code, $e);
    }
}
