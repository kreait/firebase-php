<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\CreateActionLink;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Kreait\Firebase\Auth\CreateActionLink;
use Kreait\Firebase\Util\JSON;

final class GuzzleApiClientHandler implements Handler
{
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function handle(CreateActionLink $action): string
    {
        $request = new ApiRequest($action);

        try {
            $response = $this->client->send($request, ['http_errors' => false]);
        } catch (GuzzleException $e) {
            throw new FailedToCreateActionLink('Failed to create action link: '.$e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw FailedToCreateActionLink::withActionAndResponse($action, $response);
        }

        try {
            $data = JSON::decode((string) $response->getBody(), true);
        } catch (InvalidArgumentException $e) {
            throw new FailedToCreateActionLink('Unable to parse the response data: '.$e->getMessage(), $e->getCode(), $e);
        }

        if (!($actionCode = $data['oobLink'] ?? null)) {
            throw new FailedToCreateActionLink('The response did not contain an action link');
        }

        return (string) $actionCode;
    }
}
