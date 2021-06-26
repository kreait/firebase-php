<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\CreateSessionCookie;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Kreait\Firebase\Auth\CreateSessionCookie;
use Kreait\Firebase\Util\JSON;

final class GuzzleApiClientHandler implements Handler
{
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function handle(CreateSessionCookie $action): string
    {
        $request = new ApiRequest($action);

        try {
            $response = $this->client->send($request, ['http_errors' => false]);
        } catch (GuzzleException $e) {
            throw new FailedToCreateSessionCookie($action, null, 'Connection error', 0, $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw FailedToCreateSessionCookie::withActionAndResponse($action, $response);
        }

        try {
            /** @var array{sessionCookie: string} $data */
            $data = JSON::decode((string) $response->getBody(), true);
        } catch (\InvalidArgumentException $e) {
            throw new FailedToCreateSessionCookie($action, $response, 'Unable to parse the response data: '.$e->getMessage(), 0, $e);
        }

        if ($sessionCookie = $data['sessionCookie']) {
            return $sessionCookie;
        }

        throw new FailedToCreateSessionCookie($action, $response, 'The response did not contain a session cookie');
    }
}
