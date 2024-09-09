<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\CreateActionLink;

use Beste\Json;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use Kreait\Firebase\Auth\CreateActionLink;
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

    public function handle(CreateActionLink $action): string
    {
        $request = $this->createRequest($action);

        try {
            $response = $this->client->send($request, ['http_errors' => false]);
        } catch (ClientExceptionInterface $e) {
            throw new FailedToCreateActionLink('Failed to create action link: '.$e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw FailedToCreateActionLink::withActionAndResponse($action, $response);
        }

        try {
            $data = Json::decode((string) $response->getBody(), true);
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
        $data = array_filter([
            'requestType' => $action->type(),
            'email' => $action->email(),
            'returnOobLink' => true,
        ]) + $action->settings()->toArray();

        if ($tenantId = $action->tenantId()) {
            $urlBuilder = TenantAwareAuthResourceUrlBuilder::forProjectAndTenant($this->projectId, $tenantId);
        } else {
            $urlBuilder = ProjectAwareAuthResourceUrlBuilder::forProject($this->projectId);
        }

        $url = $urlBuilder->getUrl('/accounts:sendOobCode');

        $body = Utils::streamFor(Json::encode($data, JSON_FORCE_OBJECT));

        $headers = array_filter([
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Length' => (string) $body->getSize(),
            'X-Firebase-Locale' => $action->locale(),
        ]);

        return new Request('POST', $url, $headers, $body);
    }
}
