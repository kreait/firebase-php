<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\SendActionLink;

use InvalidArgumentException;
use Kreait\Firebase\Auth\SendActionLink;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class FailedToSendActionLink extends RuntimeException implements FirebaseException
{
    /** @var SendActionLink|null */
    private $action;

    /** @var ResponseInterface|null */
    private $response;

    public static function withActionAndResponse(SendActionLink $action, ResponseInterface $response): self
    {
        $fallbackMessage = 'Failed to send action link';

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
     * @return SendActionLink|null
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
