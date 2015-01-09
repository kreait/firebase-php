<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreait\Firebase;

use Ivory\HttpAdapter\CurlHttpAdapter;
use Ivory\HttpAdapter\HttpAdapterException;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Ivory\HttpAdapter\Message\RequestInterface;

class Firebase implements FirebaseInterface
{
    use \Psr\Log\LoggerAwareTrait;

    /**
     * The HTTP adapter.
     *
     * @var HttpAdapterInterface
     */
    private $http;

    /**
     * Firebase client initialization.
     *
     * @param  string               $baseUrl The Firebase app base URL.
     * @param  HttpAdapterInterface $http    The HTTP adapter.
     * @throws FirebaseException    When the base URL is not valid.
     */
    public function __construct($baseUrl, HttpAdapterInterface $http = null)
    {
        $this->logger = new \Psr\Log\NullLogger();
        $this->http = $http ?: new CurlHttpAdapter();

        $configuration = $this->http->getConfiguration();
        $configuration->setBaseUrl(Utils::normalizeBaseUrl($baseUrl));
        $configuration->setKeepAlive(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return $this->http->getConfiguration()->getBaseUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function get($location = null)
    {
        return $this->send($location, RequestInterface::METHOD_GET);
    }

    /**
     * {@inheritdoc}
     */
    public function set($data, $location)
    {
        return $this->send($location, RequestInterface::METHOD_PUT, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function push($data, $location)
    {
        return $this->send($location, RequestInterface::METHOD_POST, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function update($data, $location)
    {
        return $this->send($location, RequestInterface::METHOD_PATCH, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($location)
    {
        return $this->send($location, RequestInterface::METHOD_DELETE);
    }

    /**
     * Sends the request and returns the processed response data.
     *
     * @param string            $location The location.
     * @param string            $method   The HTTP method.
     * @param array|object|null $data     The data.
     *
     * @throws FirebaseException
     *
     * @return array|string|void The processed response data.
     */
    private function send($location, $method, $data = null)
    {
        $location = (string) $location; // In case it is null

        if ($data && !is_array($data) && !is_object($data)) {
            throw new \InvalidArgumentException(sprintf('array or object expected, %s given'), gettype($data));
        }

        // When $location is null, the relative URL will be '/.json', which is okay
        $relativeUrl = sprintf('/%s.json', Utils::normalizeLocation($location));

        $headers = [
            'accept' => 'application/json',
            'accept-charset' => 'utf-8',
        ];

        // It would have been easier to write $this->http->send(…) but this would not give us a request object
        // to debug later
        $request = $this->http->getConfiguration()->getMessageFactory()->createRequest(
            $relativeUrl,
            $method,
            RequestInterface::PROTOCOL_VERSION_1_1,
            $headers,
            json_encode($data)
        );

        $fullUrl = sprintf('%s%s', $this->getBaseUrl(), $relativeUrl);
        $this->logger->debug(
            sprintf('%s request to %s', $method, $fullUrl),
            ($data) ? ['data_sent' => $data] : []
        );

        try {
            $response = $this->http->sendRequest($request);
        } catch (HttpAdapterException $e) {
            $this->logger->error($e->getMessage());

            $response = $e->hasResponse() ? $e->getResponse() : null;
            throw FirebaseException::httpError($request, $response);
        }

        switch ($response->getStatusCode()) {
            case 400:
                $this->logger->warning('Invalid location or PUT/POST data');
                throw FirebaseException::invalidDataOrLocation($request, $response);
            case 403:
                $this->logger->warning('Forbidden');
                throw FirebaseException::forbiddenAction($request, $response);
            case 404:
                $this->logger->warning('Request made of HTTP instead of HTTPS');
                throw FirebaseException::requestMadeOverHttpsInsteadOfHttp($request, $response);
            case 417:
                $this->logger->warning('No namespace specified');
                throw FirebaseException::noNameSpaceSpecified($request, $response);
        }

        if (!$response->hasBody()) {
            //            $this->logger->debug(
//                sprintf('Received valid, empty response from %s request to %s', $method, $relativeUrl)
//            );

            return;
        }

        $contents = $response->getBody()->getContents();
//        $this->logger->debug(
//            sprintf('Received valid response from %s request to %s', $method, $relativeUrl)
//        );

        return json_decode($contents, true);
    }
}
