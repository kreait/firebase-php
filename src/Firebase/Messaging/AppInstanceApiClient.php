<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Exception\MessagingException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

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

    /**
     * @param Topic|string $topic
     * @param RegistrationToken[]|string[] $tokens
     *
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function subscribeToTopic($topic, array $tokens): ResponseInterface
    {
        return $this->request('POST', '/iid/v1:batchAdd', [
            'json' => [
                'to' => '/topics/'.$topic,
                'registration_tokens' => $tokens,
            ],
        ]);
    }

    /**
     * @param Topic|string $topic
     * @param RegistrationToken[]|string[] $tokens
     *
     * @throws FirebaseException
     * @throws MessagingException
     */
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
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function getAppInstance(string $registrationToken): ResponseInterface
    {
        return $this->request('GET', '/iid/'.$registrationToken.'?details=true');
    }

    /**
     * @throws FirebaseException
     * @throws MessagingException
     */
    private function request(string $method, string $endpoint, array $options = null): ResponseInterface
    {
        try {
            return $this->client->request($method, $endpoint, $options ?? []);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}
