<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\RemoteConfig\ApiConnectionFailed;
use Kreait\Firebase\Exception\RemoteConfig\OperationAborted;
use Kreait\Firebase\Exception\RemoteConfig\PermissionDenied;
use Kreait\Firebase\Exception\RemoteConfig\RemoteConfigError;
use Kreait\Firebase\Exception\RemoteConfig\ValidationFailed;
use Kreait\Firebase\Exception\RemoteConfig\VersionMismatch;
use Kreait\Firebase\Http\ErrorResponseParser;
use Throwable;

use function mb_stripos;

/**
 * @internal
 */
class RemoteConfigApiExceptionConverter
{
    private readonly ErrorResponseParser $responseParser;

    public function __construct()
    {
        $this->responseParser = new ErrorResponseParser();
    }

    public function convertException(Throwable $exception): RemoteConfigException
    {
        if ($exception instanceof RequestException) {
            return $this->convertGuzzleRequestException($exception);
        }

        if ($exception instanceof ConnectException) {
            return new ApiConnectionFailed('Unable to connect to the API: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        return new RemoteConfigError($exception->getMessage(), $exception->getCode(), $exception);
    }

    private function convertGuzzleRequestException(RequestException $e): RemoteConfigException
    {
        $message = $e->getMessage();
        $code = $e->getCode();
        $response = $e->getResponse();

        if ($response !== null) {
            $message = $this->responseParser->getErrorReasonFromResponse($response);
            $code = $response->getStatusCode();
        }

        if (mb_stripos($message, 'permission_denied') !== false) {
            return new PermissionDenied($message, $code, $e);
        }

        if (mb_stripos($message, 'aborted') !== false) {
            return new OperationAborted($message, $code, $e);
        }

        if (mb_stripos($message, 'version_mismatch') !== false) {
            return new VersionMismatch($message, $code, $e);
        }

        if (mb_stripos($message, 'validation_error') !== false) {
            return new ValidationFailed($message, $code, $e);
        }

        return new RemoteConfigError($message, $code, $e);
    }
}
