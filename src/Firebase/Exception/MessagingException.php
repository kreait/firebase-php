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

    public static function fromRequestException(RequestException $e): self
    {
        $message = $e->getMessage();

        switch ($code = $e->getCode()) {
            case 400:
                $error = new InvalidMessage($message, $code);
                break;
            case 401:
            case 403:
                $error = new AuthenticationError($message, $code);
                break;
            case 404:
                $error = new NotFound($message, $code);
                break;
            case 500:
                $error = new ServerError($message, $code);
                break;
            case 503:
                $error = new ServerUnavailable($message, $code);
                break;
            default:
                $error = new UnknownError($message, $code);
                break;
        }

        if ($e->hasResponse()) {
            $error = $error->withResponse($e->getResponse());
        }

        return $error;
    }

    public function errors(): array
    {
        if (!$this->response || !JSON::isValid($body = $this->response->getBody())) {
            return [];
        }

        return JSON::decode($body, true);
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
        $e->response = $response;

        return $e;
    }
}
