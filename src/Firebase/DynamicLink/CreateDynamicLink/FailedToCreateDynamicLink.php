<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\CreateDynamicLink;

use Kreait\Firebase\DynamicLink\CreateDynamicLink;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class FailedToCreateDynamicLink extends RuntimeException implements FirebaseException
{
    /** @var CreateDynamicLink|null */
    private $action;

    /** @var ResponseInterface|null */
    private $response;

    public static function withActionAndResponse(CreateDynamicLink $action, ResponseInterface $response): self
    {
        $message = JSON::decode((string) $response->getBody(), true)['error']['message'] ?? 'Failed to create dynamic link';

        $error = new self($message);
        $error->action = $action;
        $error->response = $response;

        return $error;
    }

    /**
     * @return CreateDynamicLink|null
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
