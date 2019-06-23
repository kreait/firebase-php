<?php

namespace Kreait\Firebase\Exception;

use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\Auth\CredentialsMismatch;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\EmailNotFound;
use Kreait\Firebase\Exception\Auth\InvalidCustomToken;
use Kreait\Firebase\Exception\Auth\InvalidPassword;
use Kreait\Firebase\Exception\Auth\MissingPassword;
use Kreait\Firebase\Exception\Auth\OperationNotAllowed;
use Kreait\Firebase\Exception\Auth\PhoneNumberExists;
use Kreait\Firebase\Exception\Auth\UserDisabled;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\Auth\WeakPassword;
use Kreait\Firebase\Util\JSON;

class AuthException extends \RuntimeException implements FirebaseException
{
    public static $errors = [
        CredentialsMismatch::IDENTIFER => CredentialsMismatch::class,
        EmailExists::IDENTIFIER => EmailExists::class,
        EmailNotFound::IDENTIFIER => EmailNotFound::class,
        InvalidCustomToken::IDENTIFER => InvalidCustomToken::class,
        InvalidPassword::IDENTIFIER => InvalidPassword::class,
        MissingPassword::IDENTIFIER => MissingPassword::class,
        OperationNotAllowed::IDENTIFER => OperationNotAllowed::class,
        UserDisabled::IDENTIFER => UserDisabled::class,
        UserNotFound::IDENTIFIER => UserNotFound::class,
        WeakPassword::IDENTIFIER => WeakPassword::class,
        PhoneNumberExists::IDENTIFIER => PhoneNumberExists::class,
    ];

    /**
     * @param RequestException $e
     *
     * @return self
     */
    public static function fromRequestException(RequestException $e): self
    {
        $message = $e->getMessage();

        if ($e->getResponse() && JSON::isValid($responseBody = (string) $e->getResponse()->getBody())) {
            $errors = JSON::decode($responseBody, true);
            $message = $errors['error']['message'] ?? $message;
        }

        $candidates = \array_filter(\array_map(function ($key, $class) use ($message, $e) {
            return \stripos($message, $key) !== false
                ? new $class($e->getCode(), $e)
                : null;
        }, \array_keys(self::$errors), self::$errors));

        $fallback = new static($message, $e->getCode(), $e);

        return \array_shift($candidates) ?? $fallback;
    }
}
