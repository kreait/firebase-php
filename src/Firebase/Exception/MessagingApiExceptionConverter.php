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
use Psr\Http\Message\ResponseInterface;
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

    public function convertResponse(ResponseInterface $response, ?Throwable $previous = null): MessagingException
    {
        $code = $response->getStatusCode();

        if ($code < 400) {
            throw new InvalidArgumentException('Cannot convert a non-failed response to an exception');
        }

        $errors = $this->responseParser->getErrorsFromResponse($response);
        $message = $this->responseParser->getErrorReasonFromResponse($response);

        switch ($code) {
            case 400:
                $convertedError = new InvalidMessage($message, $code, $previous);
                break;
            case 401:
            case 403:
                $convertedError = new AuthenticationError($message, $code, $previous);
                break;
            case 404:
                $convertedError = new NotFound($message, $code, $previous);
                break;
            case 500:
                $convertedError = new ServerError($message, $code, $previous);
                break;
            case 503:
                $convertedError = new ServerUnavailable($message, $code, $previous);
                break;
            default:
                $convertedError = new MessagingError($message, $code, $previous);
                break;
        }

        return $convertedError->withErrors($errors);
    }

    private function convertGuzzleRequestException(RequestException $e): MessagingException
    {
        if ($e instanceof ConnectException) {
            return new ApiConnectionFailed($e->getMessage(), $e->getCode(), $e);
        }

        if ($response = $e->getResponse()) {
            return $this->convertResponse($response, $e);
        }

        return new MessagingError($e->getMessage(), $e->getCode(), $e);
    }
}
