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

namespace Kreait\Firebase;

use Ivory\HttpAdapter\HttpAdapterException;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Ivory\HttpAdapter\Message\RequestInterface;
use Ivory\HttpAdapter\Message\ResponseInterface;
use Kreait\Firebase\Exception\FirebaseException;

class Firebase implements FirebaseInterface
{
    /**
     * The Firebase app base URL.
     *
     * @var string
     */
    private $baseUrl;

    /**
     * The Firebase configuration.
     *
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * The current authentication token.
     *
     * @var string
     */
    private $authToken;

    /**
     * @var HttpAdapterInterface
     */
    private $http;

    /**
     * Firebase client initialization.
     *
     * @param string                 $baseUrl       The Firebase app base URL.
     * @param ConfigurationInterface $configuration The Firebase configuration.
     *
     * @throws FirebaseException When the base URL is not valid.
     */
    public function __construct($baseUrl, ConfigurationInterface $configuration = null)
    {
        $this->baseUrl = Utils::normalizeBaseUrl($baseUrl);
        $this->configuration = $configuration ?: new Configuration();

        $this->http = $this->configuration->getHttpAdapter();
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function setConfiguration(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getReference($location)
    {
        return new Reference($this, Utils::normalizeLocation($location));
    }

    public function get($location)
    {
        $url = $this->createRequestUrl($location);
        $response = $this->send($url, RequestInterface::METHOD_GET);
        $data = json_decode((string) $response->getBody(), true);

        return $data;
    }

    public function query($location, Query $query)
    {
        $url = $this->createRequestUrl($location, $query);
        $response = $this->send($url, RequestInterface::METHOD_GET);
        $data = json_decode((string) $response->getBody(), true);

        return $data;
    }

    public function set($data, $location)
    {
        if (!is_array($data) && !is_object($data)) {
            throw FirebaseException::invalidArgument('array or object', gettype($data));
        }

        $url = $this->createRequestUrl($location);
        $response = $this->send($url, RequestInterface::METHOD_PUT, $data);

        $data = json_decode((string) $response->getBody(), true);

        return $data;
    }

    public function push($data, $location)
    {
        if (!is_array($data) && !is_object($data)) {
            throw FirebaseException::invalidArgument('array or object', gettype($data));
        }

        $url = $this->createRequestUrl($location);
        $response = $this->send($url, RequestInterface::METHOD_POST, $data);
        $data = json_decode((string) $response->getBody(), true);

        return $data['name'];
    }

    public function update($data, $location)
    {
        if (!is_array($data) && !is_object($data)) {
            throw FirebaseException::invalidArgument('array or object', gettype($data));
        }

        $url = $this->createRequestUrl($location);
        $response = $this->send($url, RequestInterface::METHOD_PATCH, $data);
        $data = json_decode((string) $response->getBody(), true);

        return $data;
    }

    public function delete($location)
    {
        $url = $this->createRequestUrl($location);
        $this->send($url, RequestInterface::METHOD_DELETE);
    }

    public function setAuthToken($authToken)
    {
        if (!is_string($authToken)) {
            throw FirebaseException::invalidAuthToken($authToken);
        }

        if ($authToken === $this->getConfiguration()->getFirebaseSecret()) {
            throw FirebaseException::authTokenIsIdenticalToSecret();
        }

        $this->authToken = $authToken;
    }

    public function getAuthToken()
    {
        if (!$this->hasAuthToken()) {
            throw FirebaseException::noAuthTokenAvailable();
        }

        return $this->authToken;
    }

    public function hasAuthToken()
    {
        return !!$this->authToken;
    }

    public function removeAuthToken()
    {
        $this->authToken = null;
    }

    /**
     * Sends the request and returns the processed response data.
     *
     * @param string            $url    The full URL to send the request to.
     * @param string            $method The HTTP method.
     * @param array|object|null $data   The data.
     *
     * @throws FirebaseException
     *
     * @return ResponseInterface The response.
     */
    private function send($url, $method, $data = null)
    {
        $logger = $this->getConfiguration()->getLogger();
        $messageFactory = $this->getConfiguration()->getHttpAdapter()->getConfiguration()->getMessageFactory();

        /** @var RequestInterface $request */
        $request = $messageFactory
            ->createRequest($url, $method)
            ->withHeader('accept', 'application/json')
            ->withHeader('accept-charset', 'utf-8');

        if ($data) {
            $request->getBody()->write(json_encode($data));
        }

        $logger->debug(
            sprintf('%s request to %s', $method, $request->getUri()),
            is_null($data) ? [] : ['data' => (string) $request->getBody()]
        );

        try {
            $response = $this->http->sendRequest($request);
        } catch (HttpAdapterException $e) {
            $fe = FirebaseException::httpAdapterError($e);
            $logger->error($fe->getMessage());
            throw $fe;
        }

        if ($response->getStatusCode() >= 400) {
            $fe = FirebaseException::httpError($request, $response);
            $logger->error($fe->getMessage());
            throw $fe;
        }

        return $response;
    }

    private function createRequestUrl($location, Query $query = null)
    {
        $url = sprintf('%s/%s.json', $this->getBaseUrl(), Utils::normalizeLocation($location));

        $params = [];
        if ($query) {
            $params = array_merge($params, $query->toArray());
        }

        if ($this->hasAuthToken()) {
            $params['auth'] = $this->getAuthToken();
        }

        if (count($params)) {
            $url .= '?'.http_build_query($params, null, '&', PHP_QUERY_RFC3986);
        }

        return $url;
    }
}
