<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Kreait\Firebase\DynamicLink\DynamicLinkStatistics;
use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink;

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

    public function handle(GetStatisticsForDynamicLink $action): DynamicLinkStatistics
    {
        $request = new ApiRequest($action);

        try {
            $response = $this->apiClient->send($request, ['http_errors' => false]);
        } catch (GuzzleException $e) {
            throw new FailedToGetStatisticsForDynamicLink('Failed to get statistics for Dynamic Link: '.$e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() === 200) {
            return DynamicLinkStatistics::fromApiResponse($response);
        }

        throw FailedToGetStatisticsForDynamicLink::withActionAndResponse($action, $response);
    }
}
