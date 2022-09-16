<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Auth;

use Kreait\Firebase\Auth\CustomTokenViaGoogleIam;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Lcobucci\JWT\UnencryptedToken;
use PHPUnit\Framework\AssertionFailedError;
use Throwable;

/**
 * @internal
 *
 * @group auth-emulator
 * @group emulator
 */
final class CustomTokenViaGoogleIamTest extends IntegrationTestCase
{
    private CustomTokenViaGoogleIam $generator;

    protected function setUp(): void
    {
        $this->generator = new CustomTokenViaGoogleIam(
            self::$serviceAccount->getClientEmail(),
            self::$factory->createApiClient(),
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
            self::fail('An exception should have been thrown');
        } catch (AuthException $e) {
            $this->addToAssertionCount(1);
        } catch (AssertionFailedError $e) {
            self::fail($e->getMessage());
        } catch (Throwable $e) {
            self::fail('An ' . AuthException::class . ' should have been thrown');
        }
    }

    public function testCreateCustomTokenWithATenantId(): void
    {
        $generator = new CustomTokenViaGoogleIam(
            self::$serviceAccount->getClientEmail(),
            self::$factory->createApiClient(),
            $tenantId = IntegrationTestCase::TENANT_ID,
        );

        $customToken = $generator->createCustomToken('some-uid');

        self::assertInstanceOf(UnencryptedToken::class, $customToken);
        self::assertSame($tenantId, $customToken->claims()->get('tenantId'));
    }
}
