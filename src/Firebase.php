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
use Ivory\HttpAdapter\Message\ResponseInterface;
use Kreait\Firebase\Exception\FirebaseException;
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
    public function getReference($location)
    {
        $reference = new Reference($this, Utils::normalizeLocation($location));
        $reference->setLogger($this->logger);

        return $reference;
    }

    /**
     * {@inheritdoc}
     */
    public function get($location)
    {
        return $this->send($location, RequestInterface::METHOD_GET);
    }

    /**
     * {@inheritdoc}
     */
    public function query($location, Query $query)
    {
        return $this->send($location, RequestInterface::METHOD_GET, null, $query);
    }

    /**
     * {@inheritdoc}
     */
    public function set(array $data, $location)
    {
        return $this->send($location, RequestInterface::METHOD_PUT, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function push(array $data, $location)
    {
        $result = $this->send($location, RequestInterface::METHOD_POST, $data);

        return $result['name'];
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $data, $location)
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
     * @param string     $location The location.
     * @param string     $method   The HTTP method.
     * @param array|null $data     The data.
     * @param Query|null $query    The query.
     *
     * @throws FirebaseException
     *
     * @return array The processed response data.
     */
    private function send($location, $method, array $data = null, Query $query = null)
    {
        // When $location is null, the relative URL will be '/.json', which is okay
        $relativeUrl = sprintf('/%s.json', Utils::prepareLocationForRequest($location));
        if ($query) {
            $queryString = (string) $query;
            if (!empty($queryString)) {
                $relativeUrl = sprintf('%s?%s', $relativeUrl, $queryString);
            }
        }
        $absoluteUrl = sprintf('%s%s', $this->baseUrl, $relativeUrl);

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
            $fe = FirebaseException::httpAdapterError($e);
            $this->logger->error($fe->getMessage());
            throw $fe;
        }

        if ($response->getStatusCode() >= 400) {
            $fe = FirebaseException::httpError($request, $response);
            $this->logger->error($fe->getMessage());
            throw $fe;
        }

        return $this->getResultFromResponse($response);
    }

    /**
     * @param  ResponseInterface $response
     * @return array|void
     */
    private function getResultFromResponse(ResponseInterface $response)
    {
        $result = [];

        if ($response->hasBody()) {
            $contents = $response->getBody()->getContents();
            $result = json_decode($contents, true);
        }

        if (is_array($result)) {
            $result = $this->cleanupData($result);
        }

        return $result;
    }

    /**
     * Removes empty values from the dataset.
     *
     * @param  array $data
     * @return array
     */
    private function cleanupData(array $data)
    {
        $newData = [];

        foreach ($data as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if (is_array($value)) {
                $newData[$key] = $this->cleanupData($value);
                continue;
            }

            $newData[$key] = $value;
        }

        return $newData;
    }
}
