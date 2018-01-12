<?php

namespace Kreait\Firebase\Exception;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ApiException extends \RuntimeException implements FirebaseException
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface|null
     */
    private $response;

    public static function wrapThrowable(\Throwable $e): self
    {
        if (!($e instanceof RequestException)) {
            return new self($e->getMessage(), $e->getCode(), $e);
        }

        $request = $e->getRequest();
        $response = $e->getResponse();
        $message = self::getPreciseMessage($response) ?: $e->getMessage();
        $code = $e->getCode();

        $class = self::getTargetClassFromStatusCode($code);

        $instance = new $class($message, $code, $e);
        $instance->request = $request;
        $instance->response = $response;

        return $instance;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    private static function getTargetClassFromStatusCode($code): string
    {
        if (\in_array($code, [StatusCode::STATUS_UNAUTHORIZED, StatusCode::STATUS_FORBIDDEN], true)) {
            return PermissionDenied::class;
        }

        return static::class;
    }

    private static function getPreciseMessage(ResponseInterface $response = null)
    {
        if ($response && JSON::isValid($responseBody = (string) $response->getBody())) {
            $json = JSON::decode($responseBody, true);
            return $json['error'] ?? null;
        }
    }
}
