<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\SendActionLink;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Kreait\Firebase\Auth\SendActionLink;
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

    public function handle(SendActionLink $action): void
    {
        $request = $this->createRequest($action);

        try {
            $response = $this->client->send($request, ['http_errors' => false]);
        } catch (GuzzleException $e) {
            throw new FailedToSendActionLink('Failed to send action link: '.$e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw FailedToSendActionLink::withActionAndResponse($action, $response);
        }
    }

    private function createRequest(SendActionLink $action): RequestInterface
    {
        $data = \array_filter([
            'requestType' => $action->type(),
            'email' => $action->email(),
        ]) + $action->settings()->toArray();

        if ($tenantId = $action->tenantId()) {
            $uri = "https://identitytoolkit.googleapis.com/v1/projects/{$this->projectId}/tenants/{$tenantId}/accounts:sendOobCode";
            $data['tenantId'] = $tenantId;
        } else {
            $uri = "https://identitytoolkit.googleapis.com/v1/projects/{$this->projectId}/accounts:sendOobCode";
        }

        if ($idTokenString = $action->idTokenString()) {
            $data['idToken'] = $idTokenString;
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
