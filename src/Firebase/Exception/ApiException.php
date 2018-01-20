<?php

namespace Kreait\Firebase\Exception;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

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

    public function __construct(RequestInterface $request, string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->request = $request;
    }

    public static function wrapRequestException(RequestException $e): self
    {
        $request = $e->getRequest();
        $response = $e->getResponse();
        $message = self::getPreciseMessage($response, $default = $e->getMessage());
        $code = $e->getCode();

        $class = self::getTargetClassFromStatusCode($code);

        $instance = new $class($request, $message, $code, $e);
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

    private static function getPreciseMessage(ResponseInterface $response = null, string $default = ''): string
    {
        $message = $default;

        if ($response && JSON::isValid($responseBody = (string) $response->getBody())) {
            $message = JSON::decode($responseBody, true)['error'] ?? null;
        }

        return $message;
    }
}
