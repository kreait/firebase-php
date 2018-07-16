<?php

namespace Kreait\Firebase\RemoteConfig;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\RemoteConfigException;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getTemplate(): ResponseInterface
    {
        return $this->request('GET', 'remoteConfig');
    }

    public function publishTemplate(Template $template): ResponseInterface
    {
        return $this->request('PUT', 'remoteConfig', [
            'headers' => [
                'Content-Type' => 'application/json; UTF-8',
                'If-Match' => $template->getEtag(),
            ],
            'body' => JSON::encode($template),
        ]);
    }

    private function request($method, $uri, array $options = null)
    {
        $options = $options ?? [];

        $options = array_merge($options, [
            'decode_content' => 'gzip', // sets content-type and deflates response body
        ]);

        try {
            // GuzzleException is a marker interface that we cannot catch (at least not in <7.1)
            /** @noinspection PhpUnhandledExceptionInspection */
            return $this->client->request($method, $uri, $options);
        } catch (RequestException $e) {
            throw RemoteConfigException::fromRequestException($e);
        } catch (\Throwable $e) {
            throw new RemoteConfigException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
