<?php

namespace Kreait\Firebase\Exception;

use Kreait\Firebase\Exception\IdentityPlatform\IdentityPlatformError;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Http\ErrorResponseParser;
use Throwable;
use Kreait\Firebase\Exception\IdentityPlatform\ApiConnectionFailed;
use Kreait\Firebase\Exception\IdentityPlatform\ConfigurationExists;
use Kreait\Firebase\Exception\IdentityPlatform\ConfigurationNotFound;

/**
 * @internal
 */
class IdentityPlatformApiExceptionConverter
{
    private ErrorResponseParser $responseParser;

    /**
     * @internal
     */
    public function __construct()
    {
        $this->responseParser = new ErrorResponseParser();
    }

    public function convertException(Throwable $exception): IdentityPlatformException
    {
        // @phpstan-ignore-next-line
        if ($exception instanceof RequestException && !($exception instanceof ConnectException)) {
            return $this->convertGuzzleRequestException($exception);
        }

        if ($exception instanceof ConnectException) {
            return new ApiConnectionFailed('Unable to connect to the API: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        return new IdentityPlatformError($exception->getMessage(), $exception->getCode(), $exception);
    }

    private function convertGuzzleRequestException(RequestException $e): IdentityPlatformException
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        if ($response = $e->getResponse()) {
            $message = $this->responseParser->getErrorReasonFromResponse($response);
            $code = $response->getStatusCode();
        }

        if (\mb_stripos($message, 'configuration_exists') !== false) {
            return new ConfigurationExists('The configuration already exists.', $code, $e);
        }
        if (\mb_stripos($message, 'configuration_not_found') !== false) {
            return new ConfigurationNotFound('The configuration is not found .', $code, $e);
        }

        return new IdentityPlatformError($message, $code, $e);
    }
}
