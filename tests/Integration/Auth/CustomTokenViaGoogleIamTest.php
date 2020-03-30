<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Auth;

use Kreait\Firebase\Auth\CustomTokenViaGoogleIam;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Tests\IntegrationTestCase;
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
        $generator = new CustomTokenViaGoogleIam('user@domain.tld', self::$factory->createApiClient());

        try {
            $generator->createCustomToken('some-uid', ['kid' => '$&§']);
            $this->fail('An exception should have been thrown');
        } catch (AuthException $e) {
            $this->addToAssertionCount(1);
        } catch (AssertionFailedError $e) {
            $this->fail($e->getMessage());
        } catch (Throwable $e) {
            echo \get_class($e);
            $this->fail('An '.AuthException::class.' should have been thrown');
        }
    }
}
