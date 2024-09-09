<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;

use Beste\Json;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;
use Kreait\Firebase\Exception\RuntimeException;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

final class FailedToShortenLongDynamicLink extends RuntimeException
{
    private ?ShortenLongDynamicLink $action = null;
    private ?ResponseInterface $response = null;

    public static function withActionAndResponse(ShortenLongDynamicLink $action, ResponseInterface $response): self
    {
        $fallbackMessage = 'Failed to shorten long dynamic link';

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

    public function action(): ?ShortenLongDynamicLink
    {
        return $this->action;
    }

    public function response(): ?ResponseInterface
    {
        return $this->response;
    }
}
