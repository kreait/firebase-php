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
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Firebase implements FirebaseInterface
{
    use LoggerAwareTrait;

    /**
     * The HTTP adapter.
     *
     * @var HttpAdapterInterface
     */
    private $http;

    /**
     * The base URL
     *
     * @var string
     */
    private $baseUrl;

    /**
     * Firebase client initialization.
     *
     * @param  string               $baseUrl The Firebase app base URL.
     * @param  HttpAdapterInterface $http    The HTTP adapter.
     * @throws FirebaseException    When the base URL is not valid.
     */
    public function __construct($baseUrl, HttpAdapterInterface $http = null)
    {
        $this->logger = new NullLogger();
        $this->http = $http ?: new CurlHttpAdapter();
        $this->baseUrl = Utils::normalizeBaseUrl($baseUrl);

        $configuration = $this->http->getConfiguration();

        $configuration->setKeepAlive(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * {@inheritdoc}
     */
    public function get($location, array $options = [])
    {
        return $this->send($location, RequestInterface::METHOD_GET, null, $options);
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
        $data = $this->send($location, RequestInterface::METHOD_POST, $data);
        return $data['name'];
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
        $this->send($location, RequestInterface::METHOD_DELETE);
    }

    /**
     * Sends the request and returns the processed response data.
     *
     * @param string            $location The location.
     * @param string            $method   The HTTP method.
     * @param array|object|null $data     The data.
     * @param array             $options  Request options
     *
     * @throws FirebaseException
     *
     * @return array|string|void The processed response data.
     */
    private function send($location, $method, $data = null, array $options = [])
    {
        $location = (string) $location; // In case it is null

        if ($data && !is_array($data) && !is_object($data)) {
            throw new \InvalidArgumentException(sprintf('array or object expected, %s given'), gettype($data));
        }

        // When $location is null, the relative URL will be '/.json', which is okay
        $relativeUrl = sprintf('/%s.json', Utils::normalizeLocation($location));

        $requestParams = $this->createRequestParams($method, $options);
        if (count($requestParams)) {
            $relativeUrl = sprintf('%s?%s', $relativeUrl, http_build_query($requestParams, '', '&'));
        }

        $absoluteUrl = sprintf('%s%s', $this->getBaseUrl(), $relativeUrl);

        $headers = [
            'accept' => 'application/json',
            'accept-charset' => 'utf-8',
        ];

        // It would have been easier to write $this->http->send(…) but this would not
        // give us a request object to debug later
        $request = $this->http->getConfiguration()->getMessageFactory()->createRequest(
            $absoluteUrl,
            $method,
            RequestInterface::PROTOCOL_VERSION_1_1,
            $headers,
            json_encode($data)
        );

        $this->logger->debug(
            sprintf('%s request to %s', $method, $request->getUrl()),
            ($data) ? ['data_sent' => $data] : []
        );

        try {
            $response = $this->http->sendRequest($request);
        } catch (HttpAdapterException $e) {
            $response = $e->hasResponse() ? $e->getResponse() : null;

            $fe = FirebaseException::serverError($request, $response, $e);
            $this->logger->error($fe->getMessage());
            throw $fe;
        }

        if ($response->getStatusCode() >= 400) {
            $fe = FirebaseException::serverError($request, $response);
            $this->logger->error($fe->getMessage());
            throw $fe;
        }

        $contents = null;
        if ($response->hasBody()) {
            $contents = $response->getBody()->getContents();
        }

        return json_decode($contents, true);
    }

    /**
     * Returns an array of request parameters, based on the given method and options.
     *
     * @param string $method
     * @param array $options
     * @return array The request params.
     */
    private function createRequestParams($method, array $options)
    {
        $requestParams = [];
        switch($method) {
            case RequestInterface::METHOD_GET:
                if (isset($options['shallow']) && true === $options['shallow']) {
                    $requestParams['shallow'] = 'true';
                }
                break;
        }

        return $requestParams;
    }
}
