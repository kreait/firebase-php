<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\CreateSessionCookie;

use Beste\Json;
use InvalidArgumentException;
use Kreait\Firebase\Auth\CreateSessionCookie;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class FailedToCreateSessionCookie extends RuntimeException implements AuthException
{
    public function __construct(
        private readonly CreateSessionCookie $action,
        private readonly ?ResponseInterface $response,
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ) {
        $message ??= '';
        $code ??= 0;

        parent::__construct($message, $code, $previous);
    }

    public static function withActionAndResponse(CreateSessionCookie $action, ResponseInterface $response): self
    {
        $fallbackMessage = 'Failed to create session cookie';

        try {
            $message = Json::decode((string) $response->getBody(), true)['error']['message'] ?? $fallbackMessage;
        } catch (InvalidArgumentException) {
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
