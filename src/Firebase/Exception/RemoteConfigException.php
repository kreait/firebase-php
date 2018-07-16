<?php

namespace Kreait\Firebase\Exception;

use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\RemoteConfig\OperationAborted;
use Kreait\Firebase\Exception\RemoteConfig\PermissionDenied;
use Kreait\Firebase\Util\JSON;

class RemoteConfigException extends \RuntimeException implements FirebaseException
{
    public static $errors = [
        PermissionDenied::IDENTIFER => PermissionDenied::class,
        OperationAborted::IDENTIFER => OperationAborted::class,
    ];

    /**
     * @param RequestException $e
     *
     * @return self
     */
    public static function fromRequestException(RequestException $e): self
    {
        $message = $e->getMessage();

        /* @noinspection NullPointerExceptionInspection */
        if ($e->getResponse() && JSON::isValid($responseBody = (string) $e->getResponse()->getBody())) {
            $errors = JSON::decode($responseBody, true);
            $message = $errors['error']['message'] ?? $message;
        }

        $candidates = array_filter(array_map(function ($key, $class) use ($message, $e) {
            return false !== stripos($message, $key)
                ? new $class($e->getCode(), $e)
                : null;
        }, array_keys(self::$errors), self::$errors));

        $fallback = new static($message, $e->getCode(), $e);

        return array_shift($candidates) ?? $fallback;
    }
}
