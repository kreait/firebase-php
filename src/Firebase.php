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
use Ivory\HttpAdapter\Message\RequestInterface;
use Ivory\HttpAdapter\Message\ResponseInterface;
use Kreait\Firebase\Exception\FirebaseException;

class Firebase implements FirebaseInterface
{
    /**
     * The Firebase configuration.
     *
     * @var ConfigurationInterface
     */
    private $configuration;

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
        return $this->send($location, RequestInterface::METHOD_GET);
    }

    public function query($location, Query $query)
    {
        return $this->send($location, RequestInterface::METHOD_GET, null, $query);
    }

    public function set(array $data, $location)
    {
        return $this->send($location, RequestInterface::METHOD_PUT, $data);
    }

    public function push(array $data, $location)
    {
        $result = $this->send($location, RequestInterface::METHOD_POST, $data);

        return $result['name'];
    }

    public function update(array $data, $location)
    {
        return $this->send($location, RequestInterface::METHOD_PATCH, $data);
    }

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
     * @return mixed The processed response data.
     */
    private function send($location, $method, array $data = null, Query $query = null)
    {
        $logger = $this->getConfiguration()->getLogger();

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
        $http = $this->getConfiguration()->getHttpAdapter();

        $request = $http->getConfiguration()->getMessageFactory()->createRequest(
            $absoluteUrl,
            $method,
            RequestInterface::PROTOCOL_VERSION_1_1,
            $headers,
            json_encode($data)
        );

        $logger->debug(
            sprintf('%s request to %s', $method, $request->getUrl()),
            ($data) ? ['data_sent' => $data] : []
        );

        try {
            $response = $http->sendRequest($request);
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

        return $this->getResultFromResponse($response);
    }

    /**
     * @param ResponseInterface $response
     *
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

        // Make sure the data is an array
        if (empty($result)) {
            $result = [];
        }

        return $result;
    }

    /**
     * Removes empty values from the dataset.
     *
     * @param array $data
     *
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
