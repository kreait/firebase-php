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
     * The current authentication token to be used when Firebase was configured
     * with a database secret.
     *
     * @var string
     */
    private $authToken;

    /**
     * The current authentication override to be used when Firebase was configured
     * with a service account.
     *
     * This is a JSON string with the keys 'uid' and 'token'.
     *
     * @var string
     */
    private $overrideAuthToken;

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

    /**
     * Shorthand magic method for {@see getReference()}
     *
     * Makes it possible to write `$firebase->foo` instead of `$firebase->getReference('foo')`
     *
     * @param string $name
     *
     * @return Reference
     */
    public function __get($name)
    {
        return $this->getReference($name);
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

        return json_decode((string) $response->getBody(), true);
    }

    public function query($location, Query $query)
    {
        $url = $this->createRequestUrl($location, $query);
        $response = $this->send($url, RequestInterface::METHOD_GET);

        return json_decode((string) $response->getBody(), true);
    }

    public function set($data, $location)
    {
        if (!is_array($data) && !is_object($data)) {
            throw FirebaseException::invalidArgument('array or object', gettype($data));
        }

        $url = $this->createRequestUrl($location);
        $response = $this->send($url, RequestInterface::METHOD_PUT, $data);

        return json_decode((string) $response->getBody(), true);
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

        return json_decode((string) $response->getBody(), true);
    }

    public function delete($location)
    {
        $url = $this->createRequestUrl($location);
        $this->send($url, RequestInterface::METHOD_DELETE);
    }

    public function setAuthOverride($uid, array $claims = [])
    {
        $config = $this->getConfiguration();

        if ($config->hasGoogleClient()) {
            $this->authToken = null;
            $this->overrideAuthToken = json_encode([
                'uid' => $uid,
                'token' => $claims,
            ]);

            return;
        }

        if ($config->hasFirebaseSecret()) {
            $this->authToken = $config->getAuthTokenGenerator()->createCustomToken($uid, $claims);
            $this->overrideAuthToken = null;

            return;
        }

        throw new FirebaseException('You have to configure Firebase with a database secret or a service account to be able to set an auth override.');
    }

    public function removeAuthOverride()
    {
        $this->authToken = null;
        $this->overrideAuthToken = null;
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

        if ($this->getConfiguration()->hasGoogleClient()) {
            $googleClient = $this->getConfiguration()->getGoogleClient();

            $token = $googleClient->isAccessTokenExpired()
                ? $googleClient->refreshTokenWithAssertion()
                : $googleClient->getAccessToken();

            $request = $request->withHeader('Authorization', 'Bearer '.$token['access_token']);
        }

        if ($data) {
            $request->getBody()->write(json_encode($data));
        }

        $logger->debug(
            sprintf('%s request to %s', $method, $request->getUri()),
            $data === null ? [] : ['data' => (string) $request->getBody()]
        );

        try {
            $response = $this->http->sendRequest($request);
        } catch (HttpAdapterException $e) {
            $message = sprintf('HTTP Error: %s', $e->getMessage());

            $logger->error($message);
            throw new FirebaseException($message, $e->getCode(), $e);
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

        if ($this->authToken) {
            $params['auth'] = $this->authToken;
        } elseif ($this->overrideAuthToken) {
            $params['auth_variable_override'] = $this->overrideAuthToken;
        }

        if (count($params)) {
            $url .= '?'.http_build_query($params, null, '&', PHP_QUERY_RFC3986);
        }

        return $url;
    }
}
