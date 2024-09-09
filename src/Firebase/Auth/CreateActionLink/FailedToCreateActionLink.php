<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\CreateActionLink;

use Beste\Json;
use InvalidArgumentException;
use Kreait\Firebase\Auth\CreateActionLink;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\RuntimeException;
use Psr\Http\Message\ResponseInterface;

final class FailedToCreateActionLink extends RuntimeException implements AuthException
{
    private ?CreateActionLink $action = null;
    private ?ResponseInterface $response = null;

    public static function withActionAndResponse(CreateActionLink $action, ResponseInterface $response): self
    {
        $fallbackMessage = 'Failed to create action link';

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

    public function action(): ?CreateActionLink
    {
        return $this->action;
    }

    public function response(): ?ResponseInterface
    {
        return $this->response;
    }
}
