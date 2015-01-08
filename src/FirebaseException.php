<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreait\Firebase;

use Ivory\HttpAdapter\Message\ResponseInterface;
use Ivory\HttpAdapter\Message\RequestInterface;

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

    public static function baseUrlIsInvalid($url)
    {
        return new self(sprintf('The base url "%s" is invalid.', $url));
    }

    public static function baseUrlSchemeMustBeHttps($url)
    {
        return new self(sprintf('The base url must point to an https URL, "%s" given.', $url));
    }

    public static function locationIsInvalid($location)
    {
        return new self(sprintf('A location "%s" is invalid.', $location));
    }

    public static function nodeKeyContainsForbiddenChars($key, $forbiddenChars)
    {
        return new self(
            sprintf(
                'The node key "%s" contains on of the following invalid characters: %s',
                $key,
                $forbiddenChars
            )
        );
    }

    public static function locationKeyHasTooManyLevels($allowed, $given)
    {
        return new self(sprintf('A location key must not have more than %s levels, %s given.', $allowed, $given));
    }

    public static function locationKeyIsTooLong($allowed, $given)
    {
        return new self(sprintf('A location key must not be longer than %s bytes, %s bytes given.', $allowed, $given));
    }

    public static function requestMadeOverHttpsInsteadOfHttp()
    {
        return new self('Firebase requests HTTPS connections, HTTP connection given.');
    }

    public static function invalidDataOrLocation(RequestInterface $request = null, ResponseInterface $response = null)
    {
        $e = new self('Invalid location, PUT or POST data.');
        $e->setRequest($request);
        $e->setResponse($response);

        return $e;
    }

    public static function noNameSpaceSpecified(RequestInterface $request = null, ResponseInterface $response = null)
    {
        $e = new self('The API call did not specify a namespace.');
        $e->setRequest($request);
        $e->setResponse($response);

        return $e;
    }

    public static function forbiddenAction(RequestInterface $request = null, ResponseInterface $response = null)
    {
        $e = new self('Forbidden action. Please review your client settings or check the Firebase settings.');
        $e->setRequest($request);
        $e->setResponse($response);

        return $e;
    }

    public static function httpError(RequestInterface $request = null, ResponseInterface $response = null)
    {
        $e = new self('HTTP Error');
        $e->setRequest($request);
        $e->setResponse($response);

        return $e;
    }
}
