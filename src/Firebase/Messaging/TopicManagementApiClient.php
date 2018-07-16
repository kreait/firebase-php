<?php

namespace Kreait\Firebase\Messaging;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\MessagingException;
use Psr\Http\Message\ResponseInterface;

class TopicManagementApiClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function subscribeToTopic($topic, array $tokens): ResponseInterface
    {
        return $this->request('POST', '/iid/v1:batchAdd', [
            'json' => [
                'to' => '/topics/'.$topic,
                'registration_tokens' => $tokens,
            ],
        ]);
    }

    public function unsubscribeFromTopic($topic, array $tokens): ResponseInterface
    {
        return $this->request('POST', '/iid/v1:batchRemove', [
            'json' => [
                'to' => '/topics/'.$topic,
                'registration_tokens' => $tokens,
            ],
        ]);
    }

    private function request($method, $endpoint, array $options = null): ResponseInterface
    {
        try {
            // GuzzleException is a marker interface that we cannot catch (at least not in <7.1)
            /** @noinspection PhpUnhandledExceptionInspection */
            return $this->client->request($method, $endpoint, $options ?? []);
        } catch (RequestException $e) {
            throw MessagingException::fromRequestException($e);
        } catch (\Throwable $e) {
            throw new MessagingException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
