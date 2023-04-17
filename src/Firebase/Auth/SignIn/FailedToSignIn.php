<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\SignIn;

use Beste\Json;
use InvalidArgumentException;
use Kreait\Firebase\Auth\SignIn;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class FailedToSignIn extends RuntimeException implements AuthException
{
    private ?SignIn $action = null;
    private ?ResponseInterface $response = null;

    public static function withActionAndResponse(SignIn $action, ResponseInterface $response): self
    {
        $fallbackMessage = 'Failed to sign in';

        try {
            $message = Json::decode((string) $response->getBody(), true)['error']['message'] ?? $fallbackMessage;
        } catch (InvalidArgumentException) {
            $message = $fallbackMessage;
        }

        $error = new self($message);
        $error->action = $action;
        $error->response = $response;

        return $error;
    }

    public static function fromPrevious(Throwable $e): self
    {
        return new self('Sign in failed: '.$e->getMessage(), $e->getCode(), $e);
    }

    public function action(): ?SignIn
    {
        return $this->action;
    }

    public function response(): ?ResponseInterface
    {
        return $this->response;
    }
}
