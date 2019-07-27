<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Database;

use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Exception\DatabaseException;
use Kreait\Firebase\Exception\PermissionDenied as DeprecatedPermissionDenied;
use RuntimeException;
use Throwable;

final class PermissionDenied extends RuntimeException implements ApiException, DatabaseException, DeprecatedPermissionDenied
{
    /**
     * @deprecated 4.28.0
     */
    public function getRequest()
    {
        if ($previous = $this->getPreviousIfItIsARequestException()) {
            return $previous->getRequest();
        }

        return null;
    }

    /**
     * @deprecated 4.28.0
     */
    public function getResponse()
    {
        if ($previous = $this->getPreviousIfItIsARequestException()) {
            return $previous->getResponse();
        }

        return null;
    }

    /**
     * @return RequestException|null
     */
    private function getPreviousIfItIsARequestException()
    {
        if (($this instanceof Throwable) && ($previous = $this->getPrevious()) && ($previous instanceof RequestException)) {
            return $previous;
        }

        return null;
    }
}
