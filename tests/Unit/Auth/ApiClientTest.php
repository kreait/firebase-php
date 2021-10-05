<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\AuthException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ApiClientTest extends TestCase
{
    public function testItNeedsAProjectIdToBatchDeleteUsers(): void
    {
        $apiClient = new Auth\ApiClient($this->createMock(ClientInterface::class));

        $this->expectException(AuthException::class);
        $apiClient->deleteUsers(['uid-1', 'uid-2'], false);
    }
}
