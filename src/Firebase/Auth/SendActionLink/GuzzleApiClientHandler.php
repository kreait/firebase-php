<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\SendActionLink;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Kreait\Firebase\Auth\SendActionLink;

final class GuzzleApiClientHandler implements Handler
{
    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function handle(SendActionLink $action): void
    {
        $request = new ApiRequest($action);

        try {
            $response = $this->client->send($request, ['http_errors' => false]);
        } catch (GuzzleException $e) {
            throw new FailedToSendActionLink('Failed to send action link: '.$e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw FailedToSendActionLink::withActionAndResponse($action, $response);
        }
    }
}
