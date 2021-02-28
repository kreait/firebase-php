<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\Database\ApiConnectionFailed;
use Kreait\Firebase\Exception\Database\DatabaseError;
use Kreait\Firebase\Http\ErrorResponseParser;
use Throwable;

/**
 * @internal
 */
class DatabaseApiExceptionConverter
{
    /** @var ErrorResponseParser */
    private $responseParser;

    /**
     * @internal
     */
    public function __construct()
    {
        $this->responseParser = new ErrorResponseParser();
    }

    public function convertException(Throwable $exception): DatabaseException
    {
        if ($exception instanceof RequestException) {
            return $this->convertGuzzleRequestException($exception);
        }

        if ($exception instanceof ConnectException) {
            return new ApiConnectionFailed('Unable to connect to the API: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        return new DatabaseError($exception->getMessage(), $exception->getCode(), $exception);
    }

    private function convertGuzzleRequestException(RequestException $e): DatabaseException
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        if ($response = $e->getResponse()) {
            $message = $this->responseParser->getErrorReasonFromResponse($response);
            $code = $response->getStatusCode();
        }

        switch ($code) {
            case StatusCode::STATUS_UNAUTHORIZED:
            case StatusCode::STATUS_FORBIDDEN:
                return new Database\PermissionDenied($message, $code, $e);
            case StatusCode::STATUS_PRECONDITION_FAILED:
                return new Database\PreconditionFailed($message, $code, $e);
            case StatusCode::STATUS_NOT_FOUND:
                return Database\DatabaseNotFound::fromUri($e->getRequest()->getUri());
        }

        return new DatabaseError($message, $code, $e);
    }
}
