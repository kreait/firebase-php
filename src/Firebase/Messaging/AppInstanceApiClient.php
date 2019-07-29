<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
class AppInstanceApiClient
{
    /** @var ClientInterface */
    private $client;

    /** @var MessagingApiExceptionConverter */
    private $errorHandler;

    /**
     * @internal
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->errorHandler = new MessagingApiExceptionConverter();
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

    /**
     * @throws \Kreait\Firebase\Exception\FirebaseException
     */
    public function getAppInstance(string $registrationToken): ResponseInterface
    {
        return $this->request('GET', '/iid/'.$registrationToken.'?details=true');
    }

    private function request($method, $endpoint, array $options = null): ResponseInterface
    {
        try {
            return $this->client->request($method, $endpoint, $options ?? []);
        } catch (\Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}
