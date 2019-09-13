<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;

use InvalidArgumentException;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class FailedToShortenLongDynamicLink extends RuntimeException implements FirebaseException
{
    /** @var ShortenLongDynamicLink|null */
    private $action;

    /** @var ResponseInterface|null */
    private $response;

    public static function withActionAndResponse(ShortenLongDynamicLink $action, ResponseInterface $response): self
    {
        $fallbackMessage = 'Failed to shorten long dynamic link';

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
     * @return ShortenLongDynamicLink|null
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
