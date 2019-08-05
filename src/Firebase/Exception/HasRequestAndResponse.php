<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @codeCoverageIgnore
 */
trait HasRequestAndResponse
{
    /** @var RequestInterface|null */
    protected $request;

    /** @var ResponseInterface|null */
    protected $response;

    /**
     * @deprecated 4.28.0
     *
     * @return RequestInterface|null
     */
    public function getRequest()
    {
        if ($this->request) {
            return $this->request;
        }

        if ($previous = $this->getPreviousIfItIsARequestException()) {
            return $previous->getRequest();
        }

        return null;
    }

    /**
     * @deprecated 4.28.0
     *
     * @return RequestInterface|null
     */
    public function request()
    {
        return $this->getRequest();
    }

    /**
     * @deprecated 4.28.0
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        if ($this->response) {
            return $this->response;
        }

        if ($previous = $this->getPreviousIfItIsARequestException()) {
            return $previous->getResponse();
        }

        return null;
    }

    /**
     * @deprecated 4.28.0
     *
     * @return ResponseInterface|null
     */
    public function response()
    {
        return $this->getResponse();
    }

    /**
     * @return RequestException|null
     */
    private function getPreviousIfItIsARequestException()
    {
        if (($this instanceof Throwable) && ($previous = $this->getPrevious()) && ($previous instanceof RequestException)) {
            return $previous;
        }

        /** @var Throwable $previous */
        if ($previous && ($prePrevious = $previous->getPrevious()) && ($prePrevious instanceof RequestException)) {
            return $prePrevious;
        }

        return null;
    }
}
