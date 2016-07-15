<?php

/*
 * This file is part of the firebase-php package.
 *
 * (c) Jérôme Gamez <jerome@kreait.com>
 * (c) kreait GmbH <info@kreait.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Kreait\Firebase\Exception;

use Ivory\HttpAdapter\Message\RequestInterface;
use Ivory\HttpAdapter\Message\ResponseInterface;

/**
 * @link https://www.firebase.com/docs/rest/api/#section-error-conditions Firebase Error Conditions
 */
class FirebaseException extends \Exception
{
    /**
     * @var \Ivory\HttpAdapter\Message\RequestInterface|null
     */
    private $request;

    /**
     * @var \Ivory\HttpAdapter\Message\ResponseInterface|null
     */
    private $response;

    public function hasRequest()
    {
        return (bool) $this->request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest(RequestInterface $request = null)
    {
        $this->request = $request;
    }

    public function hasResponse()
    {
        return (bool) $this->response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response = null)
    {
        $this->response = $response;
    }

    public static function httpError(RequestInterface $request, ResponseInterface $response)
    {
        $requestBody = (string) $request->getBody();
        $responseBody = (string) $response->getBody();

        $message = sprintf(
            'Server error (%s) for URL %s with data "%s"',
            $response->getStatusCode(),
            $request->getUri(),
            $requestBody
        );

        if ($responseData = json_decode($responseBody, true)) {
            $specifics = isset($responseData['error']) ? $responseData['error'] : 'No specific error message';
            $message = sprintf('%s: %s', $message, $specifics);
        }

        if ($response->getStatusCode() === 401) {
            $e = new PermissionDeniedException($message);
        } else {
            $e = new self($message);
        }

        $e->setRequest($request);
        $e->setResponse($response);

        return $e;
    }

    public static function invalidArgument($expected, $given)
    {
        return new self(sprintf('Expected %s, %s given', $expected, $given));
    }
}
