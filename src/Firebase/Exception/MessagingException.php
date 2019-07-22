<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\Messaging\AuthenticationError;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\Messaging\ServerError;
use Kreait\Firebase\Exception\Messaging\ServerUnavailable;
use Kreait\Firebase\Exception\Messaging\UnknownError;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;

class MessagingException extends \RuntimeException implements FirebaseException
{
    /**
     * @var ResponseInterface|null
     */
    private $response;

    /**
     * @var array
     */
    private $errors = [];

    public static function fromRequestException(RequestException $e): self
    {
        $errors = [];
        $reasonPhrase = null;

        if ($response = $e->getResponse()) {
            $errors = self::getErrorsFromResponse($response);
            $reasonPhrase = $response->getReasonPhrase();
        }

        $code = (int) ($errors['error']['code'] ?? $e->getCode());
        $message = $errors['error']['message'] ?? $reasonPhrase;
        $error = self::createExceptionMessage($code, $message, $e);

        return $error
            ->withResponse($response)
            ->withErrors($errors);
    }

    public static function fromResponse(ResponseInterface $response): self
    {
        $reasonPhrase = null;
        $errors = self::getErrorsFromResponse($response);
        $reasonPhrase = $response->getReasonPhrase();

        $code = (int) ($errors['error']['code'] ?? $response->getStatusCode());
        $message = $errors['error']['message'] ?? $reasonPhrase;
        $error = self::createExceptionMessage($code, $message);

        return $error
            ->withResponse($response)
            ->withErrors($errors);
    }

    private static function createExceptionMessage($code, $message, $e = null)
    {
        switch ($code) {
            case 400:
                return new InvalidMessage($message ?: 'Invalid message', $code, $e);
            case 401:
            case 403:
                return new AuthenticationError($message ?: 'Authentication error', $code, $e);
            case 404:
                return new NotFound($message ?: 'Not found', $code, $e);
            case 500:
                return new ServerError($message ?: 'Server error', $code, $e);
            case 503:
                return new ServerUnavailable($message ?: 'Server unavailable', $code, $e);
            default:
                return new UnknownError($message ?: 'Unknown error', $code, $e);
        }
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function withErrors(array $errors = [])
    {
        $e = new static($this->getMessage(), $this->getCode());
        $e->response = $this->response;
        $e->errors = $errors;

        return $e;
    }

    /**
     * @return ResponseInterface|null
     */
    public function response()
    {
        return $this->response;
    }

    public function withResponse(ResponseInterface $response = null)
    {
        $e = new static($this->getMessage(), $this->getCode());
        $e->errors = $this->errors;
        $e->response = $response;

        return $e;
    }

    private static function getErrorsFromResponse(ResponseInterface $response): array
    {
        try {
            return JSON::decode((string) $response->getBody(), true);
        } catch (\InvalidArgumentException $e) {
            return [];
        }
    }
}
