<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink\CreateDynamicLink;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Kreait\Firebase\DynamicLink;
use Kreait\Firebase\DynamicLink\CreateDynamicLink;

final class GuzzleApiClientHandler implements Handler
{
    /** @var ClientInterface */
    private $apiClient;

    public function __construct(ClientInterface $client)
    {
        $this->apiClient = $client;
    }

    /**
     * @throws FailedToCreateDynamicLink
     */
    public function handle(CreateDynamicLink $action): DynamicLink
    {
        $request = new ApiRequest($action);

        try {
            $response = $this->apiClient->send($request, ['http_errors' => false]);
        } catch (GuzzleException $e) {
            throw new FailedToCreateDynamicLink('Failed to create dynamic link: '.$e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() === 200) {
            return DynamicLink::fromApiResponse($response);
        }

        throw FailedToCreateDynamicLink::withActionAndResponse($action, $response);
    }
}
