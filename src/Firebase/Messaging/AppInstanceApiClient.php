<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Util\JSON;
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
     * @see https://developers.google.com/instance-id/reference/server#manage_relationship_maps_for_multiple_app_instances
     *
     * @param array<Topic> $topics
     *
     * @return array<string, array<string, string>>
     */
    public function subscribeToTopics(array $topics, RegistrationTokens $tokens): array
    {
        $promises = [];
        $tokenStrings = $tokens->asStrings();

        foreach ($topics as $topic) {
            /** @var Topic $topic */
            $topicName = $topic->value();

            $promises[$topicName] = $this->client->requestAsync('POST', '/iid/v1:batchAdd', [
                'json' => [
                    'to' => '/topics/'.$topicName,
                    'registration_tokens' => $tokenStrings,
                ],
            ])->then(static function (ResponseInterface $response) {
                return JSON::decode((string) $response->getBody(), true);
            });
        }

        $responses = Promise\Utils::settle($promises)->wait();

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
                    $result[$topicName] = $response['value']->getMessage();
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
            /** @var Topic $topic */
            $topicName = $topic->value();

            $promises[$topicName] = $this->client->requestAsync('POST', '/iid/v1:batchRemove', [
                'json' => [
                    'to' => '/topics/'.$topicName,
                    'registration_tokens' => $tokenStrings,
                ],
            ])->then(static function (ResponseInterface $response) {
                return JSON::decode((string) $response->getBody(), true);
            });
        }

        $responses = Promise\Utils::settle($promises)->wait();

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
                    $result[$topicName] = $response['value']->getMessage();
                    break;
            }
        }

        return $result;
    }

    public function getAppInstanceAsync(RegistrationToken $registrationToken): Promise\PromiseInterface
    {
        return $this->client->requestAsync('GET', '/iid/'.$registrationToken->value().'?details=true')
            ->then(static function (ResponseInterface $response) use ($registrationToken) {
                $data = JSON::decode((string) $response->getBody(), true);

                return AppInstance::fromRawData($registrationToken, $data);
            })
            ->otherwise(function (Throwable $e) {
                return Promise\Create::rejectionFor($this->errorHandler->convertException($e));
            });
    }
}
