<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\RemoteConfig\ApiConnectionFailed;
use Kreait\Firebase\Exception\RemoteConfig\RemoteConfigError;
use Kreait\Firebase\Http\ErrorResponseParser;
use Throwable;

/**
 * @internal
 */
class RemoteConfigApiExceptionConverter
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

    /**
     * @return RemoteConfigException
     */
    public function convertException(Throwable $exception): FirebaseException
    {
        if ($exception instanceof RequestException) {
            return $this->convertGuzzleRequestException($exception);
        }

        return new RemoteConfigError($exception->getMessage(), $exception->getCode(), $exception);
    }

    private function convertGuzzleRequestException(RequestException $e): RemoteConfigException
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        if ($e instanceof ConnectException) {
            return new ApiConnectionFailed('Unable to connect to the API: '.$message, $code, $e);
        }

        $errors = [];

        if ($response = $e->getResponse()) {
            $message = $this->responseParser->getErrorReasonFromResponse($response);
            $code = $response->getStatusCode();
            $errors = $this->responseParser->getErrorsFromResponse($response);
        }

        if (\mb_stripos($message, 'permission_denied') !== false) {
            return new RemoteConfig\PermissionDenied($message, $code, $e);
        }

        if (\mb_stripos($message, 'aborted') !== false) {
            return new RemoteConfig\OperationAborted($message, $code, $e);
        }

        if (\mb_stripos($message, 'version_mismatch') !== false) {
            return new RemoteConfig\VersionMismatch($message, $code, $e);
        }

        if (\mb_stripos($message, 'validation_error') !== false) {
            return new RemoteConfig\ValidationFailed($message, $code, $e);
        }

        return new RemoteConfigError($message, $code, $e);
    }
}
