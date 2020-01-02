<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\SignIn;

use InvalidArgumentException;
use Kreait\Firebase\Auth\SignIn;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

final class FailedToSignIn extends RuntimeException implements FirebaseException
{
    /** @var SignIn|null */
    private $action;

    /** @var ResponseInterface|null */
    private $response;

    public static function withActionAndResponse(SignIn $action, ResponseInterface $response): self
    {
        $fallbackMessage = 'Failed to sign in';

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

    public static function fromPrevious(Throwable $e): self
    {
        return new self('Sign in failed: '.$e->getMessage(), $e->getCode(), $e);
    }

    /**
     * @return SignIn|null
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
