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

use Ivory\HttpAdapter\HttpAdapterException;
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
        return $this->request !== null;
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
        return $this->response !== null;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response = null)
    {
        $this->response = $response;
    }

    public static function urlIsInvalid($url)
    {
        return new self(sprintf('The url "%s" is invalid.', $url));
    }

    public static function baseUrlSchemeMustBeHttps($url)
    {
        return new self(sprintf('The base url must point to an https URL, "%s" given.', $url));
    }

    public static function locationKeyContainsForbiddenChars($key, $forbiddenChars)
    {
        return new self(
            sprintf(
                'The location key "%s" contains on of the following invalid characters: %s',
                $key,
                $forbiddenChars
            )
        );
    }

    public static function locationHasTooManyKeys($allowed, $given)
    {
        return new self(sprintf('A location key must not have more than %s keys, %s given.', $allowed, $given));
    }

    public static function locationKeyIsTooLong($allowed, $given)
    {
        return new self(sprintf('A location key must not be longer than %s bytes, %s bytes given.', $allowed, $given));
    }

    public static function httpAdapterError(HttpAdapterException $e)
    {
        return new self(sprintf('HTTP Error: %s', $e->getMessage()), null, $e);
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

        switch ($response->getStatusCode()) {
            case 401:
                $e = new PermissionDeniedException($message);
                break;
            default:
                $e = new self($message);
                break;
        }

        $e->setRequest($request);
        $e->setResponse($response);

        return $e;
    }

    public static function noAuthTokenAvailable()
    {
        return new self('No authentication token has been set.');
    }

    public static function invalidAuthToken($givenToken)
    {
        return new self(sprintf('The authentication token must be a string, %s given.', gettype($givenToken)));
    }

    public static function authTokenIsIdenticalToSecret()
    {
        return new self(
            'The auth token is identical to the firebase secret. Create an admin token instead '.
            '(see TokenGeneratorInterface::createAdminToken())'
        );
    }

    public static function invalidArgument($expected, $given)
    {
        return new self(sprintf('Expected %s, %s given', $expected, $given));
    }
}
