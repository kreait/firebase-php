<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Beste\Json;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Exception\MessagingException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @internal
 */
class AppInstanceApiClient
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly MessagingApiExceptionConverter $errorHandler,
    ) {
    }

    /**
     * @see https://developers.google.com/instance-id/reference/server#manage_relationship_maps_for_multiple_app_instances
     *
     * @param list<Topic> $topics
     *
     * @return array<string, array<string, string>>
     */
    public function subscribeToTopics(array $topics, RegistrationTokens $tokens): array
    {
        $promises = [];
        $tokenStrings = $tokens->asStrings();

        foreach ($topics as $topic) {
            $topicName = $topic->value();

            $promises[$topicName] = $this->client
                ->requestAsync('POST', '/iid/v1:batchAdd', [
                    'json' => [
                        'to' => '/topics/'.$topicName,
                        'registration_tokens' => $tokenStrings,
                    ],
                ])
                ->then(static fn(ResponseInterface $response): mixed => Json::decode((string) $response->getBody(), true))
            ;
        }

        $responses = Utils::settle($promises)->wait();

        $result = [];

        foreach ($responses as $topicName => $response) {
            $topicName = (string) $topicName;

            switch ($response['state']) {
                case 'fulfilled':
                    $topicResults = [];

                    foreach ($response['value']['results'] as $index => $tokenResult) {
                        $token = $tokenStrings[$index];

                        if (empty($tokenResult)) {
                            $topicResults[$token] = 'OK';

                            continue;
                        }

                        if (isset($tokenResult['error'])) {
                            $topicResults[$token] = $tokenResult['error'];

                            continue;
                        }

                        $topicResults[$token] = 'UNKNOWN';
                    }

                    $result[$topicName] = $topicResults;

                    break;

                case 'rejected':
                    $result[$topicName] = $response['reason']->getMessage();

                    break;
            }
        }

        return $result;
    }

    /**
     * @param array<Topic> $topics
     *
     * @return array<string, array<string, string>>
     */
    public function unsubscribeFromTopics(array $topics, RegistrationTokens $tokens): array
    {
        $promises = [];
        $tokenStrings = $tokens->asStrings();

        foreach ($topics as $topic) {
            $topicName = $topic->value();

            $promises[$topicName] = $this->client
                ->requestAsync('POST', '/iid/v1:batchRemove', [
                    'json' => [
                        'to' => '/topics/'.$topicName,
                        'registration_tokens' => $tokenStrings,
                    ],
                ])
                ->then(static fn(ResponseInterface $response): mixed => Json::decode((string) $response->getBody(), true))
            ;
        }

        $responses = Utils::settle($promises)->wait();

        $result = [];

        foreach ($responses as $topicName => $response) {
            $topicName = (string) $topicName;

            switch ($response['state']) {
                case 'fulfilled':
                    $topicResults = [];

                    foreach ($response['value']['results'] as $index => $tokenResult) {
                        $token = $tokenStrings[$index];

                        if (empty($tokenResult)) {
                            $topicResults[$token] = 'OK';

                            continue;
                        }

                        if (isset($tokenResult['error'])) {
                            $topicResults[$token] = (string) $tokenResult['error'];

                            continue;
                        }

                        $topicResults[$token] = 'UNKNOWN';
                    }

                    $result[$topicName] = $topicResults;

                    break;

                case 'rejected':
                    $result[$topicName] = $response['reason']->getMessage();

                    break;
            }
        }

        return $result;
    }

    public function getAppInstanceAsync(RegistrationToken $registrationToken): PromiseInterface
    {
        return $this->client
            ->requestAsync('GET', '/iid/'.$registrationToken->value().'?details=true')
            ->then(static function (ResponseInterface $response) use ($registrationToken): AppInstance {
                $data = Json::decode((string) $response->getBody(), true);

                return AppInstance::fromRawData($registrationToken, $data);
            })
            ->otherwise(fn(Throwable $e): PromiseInterface => Create::rejectionFor($this->errorHandler->convertException($e)))
        ;
    }

    /**
     * @throws MessagingException
     */
    public function getAppInstance(RegistrationToken $registrationToken): AppInstance
    {
        try {
            $response = $this->client->request('GET', '/iid/'.$registrationToken->value().'?details=true');
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }

        try {
            $data = Json::decode((string) $response->getBody(), true);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }

        return AppInstance::fromRawData($registrationToken, $data);
    }
}
