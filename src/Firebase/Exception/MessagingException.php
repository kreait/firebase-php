<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\Messaging\AuthenticationError;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\Messaging\ServerError;
use Kreait\Firebase\Exception\Messaging\ServerUnavailable;
use Kreait\Firebase\Exception\Messaging\UnknownError;

class MessagingException extends \RuntimeException implements FirebaseException
{
    public static function fromRequestException(RequestException $e): self
    {
        $message = $e->getMessage();

        if ($e->hasResponse()) {
            /** @noinspection NullPointerExceptionInspection */
            $message = 'Raw server response: '.$e->getResponse()->getBody()->getContents();
        }

        switch ($code = $e->getCode()) {
            case 400:
                return new InvalidArgument('Invalid Argument: '.$message, $code, $e);
            case 401:
            case 403:
                return new AuthenticationError('Authentication error: '.$message, $code, $e);
            case 500:
                return new ServerError('Server Error: '.$message, $code, $e);
            case 503:
                return new ServerUnavailable('Server Unavailable: '.$message, $code, $e);
            default:
                return new UnknownError('Unknown error: '.$message, $code);
        }
    }
}
