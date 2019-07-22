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
        $message = 'Unknown error';
        $code = $e->getCode();

        if ($response = $e->getResponse()) {
            $errors = self::getErrorsFromResponse($response);
            $message = $response->getReasonPhrase();
        }

        if (\is_array($errors['error'] ?? null)) {
            $code = (int) ($errors['error']['code'] ?? $code);
            $message = $errors['error']['message'] ?? $message;
        } elseif (\is_string($errors['error'] ?? null)) {
            $message = $errors['error'];
        }

        switch ($code) {
            case 400:
                $error = new InvalidMessage($message ?: 'Invalid message', $code, $e);
                break;
            case 401:
            case 403:
                $error = new AuthenticationError($message ?: 'Authentication error', $code, $e);
                break;
            case 404:
                $error = new NotFound($message ?: 'Not found', $code, $e);
                break;
            case 500:
                $error = new ServerError($message ?: 'Server error', $code, $e);
                break;
            case 503:
                $error = new ServerUnavailable($message ?: 'Server unavailable', $code, $e);
                break;
            default:
                $error = new UnknownError($message ?: 'Unknown error', $code, $e);
                break;
        }

        return $error
            ->withResponse($response)
            ->withErrors($errors);
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
