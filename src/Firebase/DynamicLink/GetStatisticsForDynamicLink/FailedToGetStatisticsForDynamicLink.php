<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;

use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;
use Kreait\Firebase\Exception\FirebaseException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class FailedToGetStatisticsForDynamicLink extends RuntimeException implements FirebaseException
{
    /** @var GetStatisticsForDynamicLink|null */
    private $action;

    /** @var ResponseInterface|null */
    private $response;

    public static function withActionAndResponse(GetStatisticsForDynamicLink $action, ResponseInterface $response): self
    {
        $error = new self('Failed to get statistics for dynamic link');
        $error->action = $action;
        $error->response = $response;

        return $error;
    }

    /**
     * @return GetStatisticsForDynamicLink|null
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
