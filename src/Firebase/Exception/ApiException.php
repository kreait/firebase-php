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
        if ($e instanceof self) {
            return $e;
        }

        if (!($e instanceof RequestException)) {
            return new self($e->getMessage(), $e->getCode(), $e);
        }

        $request = $e->getRequest();
        $response = $e->getResponse();
        $message = $e->getMessage();
        $code = $e->getCode();

        if (in_array($code, [StatusCode::STATUS_UNAUTHORIZED, StatusCode::STATUS_FORBIDDEN], true)) {
            $class = PermissionDenied::class;
        } else {
            $class = static::class;
        }

        if ($response) {
            $message = JSON::decode((string) $response->getBody(), true)['error'] ?? $message;
        }

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
}
