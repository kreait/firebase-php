<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;

use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;
use Kreait\Firebase\Exception\FirebaseException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class FailedToGetStatisticsForDynamicLink extends RuntimeException implements FirebaseException
{
    private ?GetStatisticsForDynamicLink $action = null;
    private ?ResponseInterface $response = null;

    public static function withActionAndResponse(GetStatisticsForDynamicLink $action, ResponseInterface $response): self
    {
        $error = new self('Failed to get statistics for dynamic link');
        $error->action = $action;
        $error->response = $response;

        return $error;
    }

    public function action(): ?GetStatisticsForDynamicLink
    {
        return $this->action;
    }

    public function response(): ?ResponseInterface
    {
        return $this->response;
    }
}
