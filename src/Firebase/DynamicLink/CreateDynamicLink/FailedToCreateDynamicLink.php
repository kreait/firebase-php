<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\CreateDynamicLink;

use Beste\Json;
use Kreait\Firebase\DynamicLink\CreateDynamicLink;
use Kreait\Firebase\Exception\FirebaseException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use UnexpectedValueException;

final class FailedToCreateDynamicLink extends RuntimeException implements FirebaseException
{
    private ?CreateDynamicLink $action = null;
    private ?ResponseInterface $response = null;

    public static function withActionAndResponse(CreateDynamicLink $action, ResponseInterface $response): self
    {
        $fallbackMessage = 'Failed to create dynamic link';

        try {
            $message = Json::decode((string) $response->getBody(), true)['error']['message'] ?? $fallbackMessage;
        } catch (UnexpectedValueException) {
            $message = $fallbackMessage;
        }

        $error = new self($message);
        $error->action = $action;
        $error->response = $response;

        return $error;
    }

    public function action(): ?CreateDynamicLink
    {
        return $this->action;
    }

    public function response(): ?ResponseInterface
    {
        return $this->response;
    }
}
