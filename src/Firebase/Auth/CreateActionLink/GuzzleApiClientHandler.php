<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\CreateActionLink;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use Kreait\Firebase\Auth\CreateActionLink;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\RequestInterface;

final class GuzzleApiClientHandler implements Handler
{
    private ClientInterface $client;
    private string $projectId;

    public function __construct(ClientInterface $client, string $projectId)
    {
        $this->client = $client;
        $this->projectId = $projectId;
    }

    public function handle(CreateActionLink $action): string
    {
        $request = $this->createRequest($action);

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

    private function createRequest(CreateActionLink $action): RequestInterface
    {
        $data = \array_filter([
            'requestType' => $action->type(),
            'email' => $action->email(),
            'returnOobLink' => true,
        ]) + $action->settings()->toArray();

        if ($tenantId = $action->tenantId()) {
            $uri = "https://identitytoolkit.googleapis.com/v1/projects/{$this->projectId}/tenants/{$tenantId}/accounts:sendOobCode";
        } else {
            $uri = "https://identitytoolkit.googleapis.com/v1/projects/{$this->projectId}/accounts:sendOobCode";
        }

        $body = Utils::streamFor(JSON::encode($data, JSON_FORCE_OBJECT));

        $headers = \array_filter([
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Length' => (string) $body->getSize(),
            'X-Firebase-Locale' => $action->locale(),
        ]);

        return new Request('POST', $uri, $headers, $body);
    }
}
