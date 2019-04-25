<?php

namespace Kreait\Firebase\Exception;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
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
        $message = null;

        if ($response) {
            $message = self::getPreciseMessage($response);
        }

        $message = $message ?: $e->getMessage();

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

    private static function getPreciseMessage(ResponseInterface $response)
    {
        $responseBody = (string) $response->getBody();

        if (!JSON::isValid($responseBody)) {
            return null;
        }

        try {
            $data = JSON::decode($responseBody, true);
        } catch (InvalidArgumentException $e) {
            return null;
        }

        if (is_string($data['error']['message'] ?? null)) {
            return $data['error']['message'];
        }

        if (is_string($data['error'] ?? null)) {
            return $data['error'];
        }

        return null;
    }
}
