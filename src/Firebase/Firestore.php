<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Kreait\Firebase\Firestore\ApiClient;

final class Firestore implements Contract\Firestore
{
    private ApiClient $client;

    private function __construct(ApiClient $apiClient)
    {
        $this->client = $apiClient;
    }

    public static function withApiClient(ApiClient $apiClient): self
    {
        return new self($apiClient);
    }

    public function database(): ApiClient
    {
        return $this->client;
    }
}
