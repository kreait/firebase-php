<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\CreateSessionCookie;

use Beste\Json;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use Kreait\Firebase\Auth\CreateSessionCookie;
use Kreait\Firebase\Auth\ProjectAwareAuthResourceUrlBuilder;
use Kreait\Firebase\Auth\TenantAwareAuthResourceUrlBuilder;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;

use function array_filter;

use const JSON_FORCE_OBJECT;

/**
 * @internal
 */
final class GuzzleApiClientHandler
{
    /**
     * @param non-empty-string $projectId
     */
    public function __construct(
        private readonly ClientInterface $client,
        private readonly string $projectId,
    ) {
    }

    public function handle(CreateSessionCookie $action): string
    {
        $request = $this->createRequest($action);

        try {
            $response = $this->client->send($request, ['http_errors' => false]);
        } catch (ClientExceptionInterface $e) {
            throw new FailedToCreateSessionCookie($action, null, 'Connection error', 0, $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw FailedToCreateSessionCookie::withActionAndResponse($action, $response);
        }

        try {
            /** @var array{sessionCookie?: string|null} $data */
            $data = Json::decode((string) $response->getBody(), true);
        } catch (InvalidArgumentException $e) {
            throw new FailedToCreateSessionCookie($action, $response, 'Unable to parse the response data: '.$e->getMessage(), 0, $e);
        }

        $sessionCookie = $data['sessionCookie'] ?? null;

        if ($sessionCookie !== null) {
            return $sessionCookie;
        }

        throw new FailedToCreateSessionCookie($action, $response, 'The response did not contain a session cookie');
    }

    private function createRequest(CreateSessionCookie $action): RequestInterface
    {
        $data = [
            'idToken' => $action->idToken(),
            'validDuration' => $action->ttlInSeconds(),
        ];

        if ($tenantId = $action->tenantId()) {
            $urlBuilder = TenantAwareAuthResourceUrlBuilder::forProjectAndTenant($this->projectId, $tenantId);
        } else {
            $urlBuilder = ProjectAwareAuthResourceUrlBuilder::forProject($this->projectId);
        }

        $url = $urlBuilder->getUrl(':createSessionCookie');

        $body = Utils::streamFor(Json::encode($data, JSON_FORCE_OBJECT));

        $headers = array_filter([
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Length' => (string) $body->getSize(),
        ]);

        return new Request('POST', $url, $headers, $body);
    }
}
