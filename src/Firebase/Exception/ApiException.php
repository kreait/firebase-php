<?php

namespace Kreait\Firebase\Exception;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Util\JSON;

class ApiException extends \RuntimeException implements FirebaseException
{
    public static function wrapThrowable(\Throwable $e): ApiException
    {
        if ($e instanceof self) {
            return $e;
        }

        $message = $e->getMessage();
        $code = $e->getCode();

        if ($e instanceof RequestException && $response = $e->getResponse()) {
            $message = JSON::decode((string) $response->getBody(), true)['error'] ?? $message;
        }

        if (in_array($code, [StatusCode::STATUS_UNAUTHORIZED, StatusCode::STATUS_FORBIDDEN], true)) {
            return new PermissionDenied($message, $code, $e);
        }

        return new self($e->getMessage(), $e->getCode(), $e);
    }
}
