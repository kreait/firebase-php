<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\CreateActionLink;

use InvalidArgumentException;
use Kreait\Firebase\Auth\CreateActionLink;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class FailedToCreateActionLink extends RuntimeException implements FirebaseException
{
    /** @var CreateActionLink|null */
    private $action;

    /** @var ResponseInterface|null */
    private $response;

    public static function withActionAndResponse(CreateActionLink $action, ResponseInterface $response): self
    {
        $fallbackMessage = 'Failed to create action link';

        try {
            $message = JSON::decode((string) $response->getBody(), true)['error']['message'] ?? $fallbackMessage;
        } catch (InvalidArgumentException $e) {
            $message = $fallbackMessage;
        }

        $error = new self($message);
        $error->action = $action;
        $error->response = $response;

        return $error;
    }

    /**
     * @return CreateActionLink|null
     */
    public function action()
    {
        return $this->action;
    }

    /**
     * @return ResponseInterface|null
     */
    public function response()
    {
        return $this->response;
    }
}
