<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @deprecated 4.28.0 catch specific exceptions or \Kreait\Firebase\Exception\FirebaseException instead
 */
interface ApiException extends FirebaseException
{
    /**
     * @deprecated 4.28.0
     *
     * @return RequestInterface|null
     */
    public function getRequest();

    /**
     * @deprecated 4.28.0
     *
     * @return ResponseInterface|null
     */
    public function getResponse();
}
