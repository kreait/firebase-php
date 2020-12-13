<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Auth;

use Kreait\Firebase\Auth\CustomTokenViaGoogleIam;
use Kreait\Firebase\Auth\TenantId;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Lcobucci\JWT\Token\Plain;
use PHPUnit\Framework\AssertionFailedError;
use Throwable;

/**
 * @internal
 */
class CustomTokenViaGoogleIamTest extends IntegrationTestCase
{
    /** @var CustomTokenViaGoogleIam */
    private $generator;

    protected function setUp(): void
    {
        if (!self::$serviceAccount) {
            $this->markTestSkipped('The integration tests require credentials');
        }

        $this->generator = new CustomTokenViaGoogleIam(
            self::$serviceAccount->getClientEmail(),
            self::$factory->createApiClient()
        );
    }

    public function testCreateCustomToken(): void
    {
        $this->generator->createCustomToken('some-uid', ['a-claim' => 'a-value']);
        $this->addToAssertionCount(1);
    }

    public function testCreateCustomTokenWithAnInvalidClientEmail(): void
    {
        $generator = new CustomTokenViaGoogleIam(self::randomEmail(__FUNCTION__), self::$factory->createApiClient());

        try {
            $generator->createCustomToken('some-uid', ['kid' => '$&§']);
            $this->fail('An exception should have been thrown');
        } catch (AuthException $e) {
            $this->addToAssertionCount(1);
        } catch (AssertionFailedError $e) {
            $this->fail($e->getMessage());
        } catch (Throwable $e) {
            $this->fail('An '.AuthException::class.' should have been thrown');
        }
    }

    public function testCreateCustomTokenWithATenantId(): void
    {
        if (!self::$serviceAccount) {
            $this->markTestSkipped('The integration tests require credentials');
        }

        $generator = new CustomTokenViaGoogleIam(
            self::$serviceAccount->getClientEmail(),
            self::$factory->createApiClient(),
            TenantId::fromString($tenantId = 'FirstTenant-fqqqc')
        );

        $customToken = $generator->createCustomToken('some-uid');

        $this->assertInstanceOf(Plain::class, $customToken);
        $this->assertSame($tenantId, $customToken->claims()->get('tenantId'));
    }
}
