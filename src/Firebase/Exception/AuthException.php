<?php

namespace Kreait\Firebase\Exception;

use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\Auth\CredentialsMismatch;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\InvalidCustomToken;
use Kreait\Firebase\Exception\Auth\MissingPassword;
use Kreait\Firebase\Exception\Auth\WeakPassword;
use Kreait\Firebase\Util\JSON;

class AuthException extends \RuntimeException implements FirebaseException
{
    public static $errors = [
        InvalidCustomToken::IDENTIFER => InvalidCustomToken::class,
        CredentialsMismatch::IDENTIFER => CredentialsMismatch::class,
        WeakPassword::IDENTIFIER => WeakPassword::class,
        EmailExists::IDENTIFIER => EmailExists::class,
        MissingPassword::IDENTIFIER => MissingPassword::class,
    ];

    /**
     * @param RequestException $e
     *
     * @return self
     */
    public static function fromRequestException(RequestException $e): self
    {
        $message = $e->getMessage();

        if (!($response = $e->getResponse())) {
            return new static($message, $e->getCode(), $e);
        }

        try {
            $errors = JSON::decode((string) $response->getBody(), true);
        } catch (\InvalidArgumentException $jsonDecodeException) {
            return new static('Invalid JSON: The API returned an invalid JSON string.', $e->getCode(), $e);
        }

        $message = $errors['error']['message'] ?? $message;

        $candidates = array_filter(array_map(function ($key, $class) use ($message, $e) {
            return stripos($message, $key) !== false ? new $class($e->getCode(), $e) : null;
        }, array_keys(self::$errors), self::$errors));

        $fallback = new static(sprintf('Unknown error: "%s"', $message), $e->getCode(), $e);

        return array_shift($candidates) ?? $fallback;
    }
}
