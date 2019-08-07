<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\Messaging\ApiConnectionFailed;
use Kreait\Firebase\Exception\Messaging\AuthenticationError;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\MessagingError;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\Messaging\ServerError;
use Kreait\Firebase\Exception\Messaging\ServerUnavailable;
use Kreait\Firebase\Http\ErrorResponseParser;
use Throwable;

/**
 * @internal
 */
class MessagingApiExceptionConverter
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
     * @return MessagingException
     */
    public function convertException(Throwable $exception): FirebaseException
    {
        if ($exception instanceof RequestException) {
            return $this->convertGuzzleRequestException($exception);
        }

        return new MessagingError($exception->getMessage(), $exception->getCode(), $exception);
    }

    private function convertGuzzleRequestException(RequestException $e): MessagingException
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        if ($e instanceof ConnectException) {
            return new ApiConnectionFailed($message, $code, $e);
        }

        $errors = [];

        if ($response = $e->getResponse()) {
            $errors = $this->responseParser->getErrorsFromResponse($response);
            $message = $this->responseParser->getErrorReasonFromResponse($response);
            $code = $response->getStatusCode();
        }

        switch ($code) {
            case 400:
                $convertedError = new InvalidMessage($message, $code, $e);
                break;
            case 401:
            case 403:
                $convertedError = new AuthenticationError($message, $code, $e);
                break;
            case 404:
                $convertedError = new NotFound($message, $code, $e);
                break;
            case 500:
                $convertedError = new ServerError($message, $code, $e);
                break;
            case 503:
                $convertedError = new ServerUnavailable($message, $code, $e);
                break;
            default:
                $convertedError = new MessagingError($message, $code, $e);
                break;
        }

        $convertedError = $convertedError->withErrors($errors);

        if ($response) {
            $convertedError = $convertedError->withResponse($response);
        }

        return $convertedError;
    }
}
