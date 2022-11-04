<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\SendActionLink;

use Beste\Json;
use InvalidArgumentException;
use Kreait\Firebase\Auth\SendActionLink;
use Kreait\Firebase\Exception\FirebaseException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class FailedToSendActionLink extends RuntimeException implements FirebaseException
{
    private ?SendActionLink $action = null;
    private ?ResponseInterface $response = null;

    public static function withActionAndResponse(SendActionLink $action, ResponseInterface $response): self
    {
        $fallbackMessage = 'Failed to send action link';

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

    public function action(): ?SendActionLink
    {
        return $this->action;
    }

    public function response(): ?ResponseInterface
    {
        return $this->response;
    }
}
