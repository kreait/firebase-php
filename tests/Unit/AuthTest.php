<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Firebase\Auth\Token\Domain\Generator;
use Firebase\Auth\Token\Domain\Verifier;
use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\AuthException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class AuthTest extends TestCase
{
    public function testItNeedsAProjectIdToBatchDeleteUsers(): void
    {
        $auth = new Auth(
            $this->createMock(Auth\ApiClient::class),
            $this->createMock(ClientInterface::class),
            $this->createMock(Generator::class),
            $this->createMock(Verifier::class),
            $this->createMock(Auth\SignIn\Handler::class),
            Auth\TenantId::fromString('tenant-id')
            // The last parameter would have been the project ID
        );

        $this->expectException(AuthException::class);
        $auth->deleteUsers(['uid-1', 'uid-2']);
    }
}
