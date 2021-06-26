<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\CreateSessionCookie;

use Kreait\Firebase\Auth\CreateSessionCookie;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class FailedToCreateSessionCookie extends \RuntimeException implements FirebaseException
{
    private CreateSessionCookie $action;
    private ?ResponseInterface $response = null;

    public function __construct(CreateSessionCookie $action, ?ResponseInterface $response, string $message = null, int $code = null, ?Throwable $previous = null)
    {
        $message = $message ?? '';
        $code = $code ?? 0;

        parent::__construct($message, $code, $previous);

        $this->action = $action;
        $this->response = $response;
    }

    public static function withActionAndResponse(CreateSessionCookie $action, ResponseInterface $response): self
    {
        $fallbackMessage = 'Failed to create session cookie';

        try {
            $message = JSON::decode((string) $response->getBody(), true)['error']['message'] ?? $fallbackMessage;
        } catch (\InvalidArgumentException $e) {
            $message = $fallbackMessage;
        }

        return new self($action, $response, $message);
    }

    public function action(): CreateSessionCookie
    {
        return $this->action;
    }

    public function response(): ?ResponseInterface
    {
        return $this->response;
    }
}
