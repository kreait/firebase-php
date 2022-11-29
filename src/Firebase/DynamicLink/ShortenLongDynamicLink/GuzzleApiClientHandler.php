<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Kreait\Firebase\DynamicLink;
use Kreait\Firebase\DynamicLink\ShortenLongDynamicLink;

/**
 * @internal
 */
final class GuzzleApiClientHandler implements Handler
{
    private ClientInterface $apiClient;

    public function __construct(ClientInterface $client)
    {
        $this->apiClient = $client;
    }

    /**
     * @throws FailedToShortenLongDynamicLink
     */
    public function handle(ShortenLongDynamicLink $action): DynamicLink
    {
        $request = new ApiRequest($action);

        try {
            $response = $this->apiClient->send($request, ['http_errors' => false]);
        } catch (GuzzleException $e) {
            throw new FailedToShortenLongDynamicLink('Failed to shorten long dynamic link: '.$e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() === 200) {
            return DynamicLink::fromApiResponse($response);
        }

        throw FailedToShortenLongDynamicLink::withActionAndResponse($action, $response);
    }
}
