<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\Database\ApiConnectionFailed;
use Kreait\Firebase\Exception\Database\DatabaseError;
use Kreait\Firebase\Exception\Database\DatabaseNotFound;
use Kreait\Firebase\Exception\Database\PermissionDenied;
use Kreait\Firebase\Exception\Database\PreconditionFailed;
use Kreait\Firebase\Http\ErrorResponseParser;
use Psr\Http\Client\NetworkExceptionInterface;
use Throwable;

/**
 * @internal
 */
class DatabaseApiExceptionConverter
{
    private readonly ErrorResponseParser $responseParser;

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

        return match ($code) {
            StatusCode::STATUS_UNAUTHORIZED, StatusCode::STATUS_FORBIDDEN => new PermissionDenied($message, $code, $e),
            StatusCode::STATUS_PRECONDITION_FAILED => new PreconditionFailed($message, $code, $e),
            StatusCode::STATUS_NOT_FOUND => DatabaseNotFound::fromUri($e->getRequest()->getUri()),
            default => new DatabaseError($message, $code, $e),
        };
    }
}
