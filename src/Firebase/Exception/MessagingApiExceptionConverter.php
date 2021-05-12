<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use DateTimeImmutable;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Kreait\Clock;
use Kreait\Clock\SystemClock;
use Kreait\Firebase\Exception\Messaging\ApiConnectionFailed;
use Kreait\Firebase\Exception\Messaging\AuthenticationError;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\MessagingError;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\Messaging\QuotaExceeded;
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
    private ErrorResponseParser $responseParser;

    private Clock $clock;

    /**
     * @internal
     */
    public function __construct(?Clock $clock = null)
    {
        $this->responseParser = new ErrorResponseParser();
        $this->clock = $clock ?? new SystemClock();
    }

    /**
     * @return MessagingException
     */
    public function convertException(Throwable $exception): FirebaseException
    {
        /* @phpstan-ignore-next-line */
        if ($exception instanceof RequestException && !($exception instanceof ConnectException)) {
            return $this->convertGuzzleRequestException($exception);
        }

        if ($exception instanceof ConnectException) {
            return new ApiConnectionFailed('Unable to connect to the API: '.$exception->getMessage(), $exception->getCode(), $exception);
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
                $convertedError = new InvalidMessage($message);

                break;

            case 401:
            case 403:
                $convertedError = new AuthenticationError($message);

                break;

            case 404:
                $convertedError = new NotFound($message);

                break;

            case 429:
                $convertedError = new QuotaExceeded($message);
                if ($retryAfter = $this->getRetryAfter($response)) {
                    $convertedError = $convertedError->withRetryAfter($retryAfter);
                }

                break;

            case 500:
                $convertedError = new ServerError($message);

                break;

            case 503:
                $convertedError = new ServerUnavailable($message);
                if ($retryAfter = $this->getRetryAfter($response)) {
                    $convertedError = $convertedError->withRetryAfter($retryAfter);
                }

                break;

            default:
                $convertedError = new MessagingError($message, $code, $previous);

                break;
        }

        return $convertedError->withErrors($errors);
    }

    private function convertGuzzleRequestException(RequestException $e): MessagingException
    {
        if ($response = $e->getResponse()) {
            return $this->convertResponse($response, $e);
        }

        return new MessagingError($e->getMessage(), $e->getCode(), $e);
    }

    private function getRetryAfter(ResponseInterface $response): ?DateTimeImmutable
    {
        $retryAfter = $response->getHeader('Retry-After')[0] ?? null;

        if (!$retryAfter) {
            return null;
        }

        if (\is_numeric($retryAfter)) {
            return $this->clock->now()->modify("+{$retryAfter} seconds");
        }

        try {
            return new DateTimeImmutable($retryAfter);
        } catch (Throwable $e) {
            // We can't afford to throw exceptions in an exception handler :)
            // Here, if the Retry-After header doesn't have a numeric value
            // or a date that can be handled by DateTimeImmutable, we just
            // throw it away, sorry not sorry ¯\_(ツ)_/¯
            return null;
        }
    }
}
