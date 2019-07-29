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

final class DatabaseApiExceptionConverter implements ExceptionConverter
{
    /** @var ErrorResponseParser */
    private $responseParser;

    public function __construct()
    {
        $this->responseParser = new ErrorResponseParser();
    }

    /**
     * @return DatabaseException
     */
    public function convertException(Throwable $exception): FirebaseException
    {
        if ($exception instanceof RequestException) {
            return $this->convertGuzzleRequestException($exception);
        }

        return new DatabaseError($exception->getMessage(), $exception->getCode(), $exception);
    }

    private function convertGuzzleRequestException(RequestException $e)
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        if ($e instanceof ConnectException) {
            return new ApiConnectionFailed('Unable to connect to the API: '.$message, $code, $e);
        }

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
        }

        return new DatabaseError($message, $code, $e);
    }
}
