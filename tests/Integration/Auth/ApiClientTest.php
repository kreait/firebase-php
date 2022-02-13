<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Auth;

use Beste\Clock\SystemClock;
use Beste\Json;
use Kreait\Firebase\Auth\ApiClient;
use Kreait\Firebase\Auth\SignIn\GuzzleHandler;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class ApiClientTest extends IntegrationTestCase
{
    private ApiClient $apiClient;

    protected function setUp(): void
    {
        $client = self::$factory->createApiClient();

        $this->apiClient = new ApiClient(
            self::$serviceAccount->getProjectId(),
            null,
            $client,
            new GuzzleHandler($client),
            SystemClock::create()
        );
    }

    public function testItDownloadsOnlyAsManyAccountsAsItIsSupposedTo(): void
    {
        $numberOfUsers = 2;

        $response = $this->apiClient->downloadAccount($numberOfUsers);
        $result = Json::decode((string) $response->getBody(), true);

        $this->assertCount($numberOfUsers, $result['users']);
    }
}
