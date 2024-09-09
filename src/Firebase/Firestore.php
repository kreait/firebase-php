<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Kreait\Firebase\Firestore\ApiClient;

/**
 * @internal
 */
final class Firestore implements Contract\Firestore
{
    private function __construct(private readonly ApiClient $apiClient)
    {
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
