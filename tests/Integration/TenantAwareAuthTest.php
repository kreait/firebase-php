<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Auth\CreateSessionCookie\FailedToCreateSessionCookie;

/**
 * @internal
 */
final class TenantAwareAuthTest extends AuthTestCase
{
    protected function setUp(): void
    {
        $this->auth = self::$factory->withTenantId(self::TENANT_ID)->createAuth();
    }

    public function testCreateSessionCookie(): void
    {
        $this->expectException(FailedToCreateSessionCookie::class);
        $this->expectExceptionMessageMatches('/UNSUPPORTED_TENANT_OPERATION/');

        parent::testCreateSessionCookie();
    }

    public function testVerifySessionCookie(): void
    {
        $this->expectException(FailedToCreateSessionCookie::class);
        $this->expectExceptionMessageMatches('/UNSUPPORTED_TENANT_OPERATION/');

        parent::testVerifySessionCookie();
    }

    public function testVerifySessionCookieAfterTokenRevocation(): void
    {
        $this->expectException(FailedToCreateSessionCookie::class);
        $this->expectExceptionMessageMatches('/UNSUPPORTED_TENANT_OPERATION/');

        parent::testVerifySessionCookieAfterTokenRevocation();
    }

    public function testNewUsersAreScopedToATenant(): void
    {
        $user = $this->auth->createUserWithEmailAndPassword(
            self::randomEmail(__FUNCTION__),
            'password123'
        );

        try {
            $this->assertSame(self::TENANT_ID, $user->tenantId);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testCustomTokensIncludeTheTenant(): void
    {
        $user = $this->auth->createAnonymousUser();

        $token = $this->auth->createCustomToken($user->uid);

        $parsed = $this->auth->parseToken($token->toString());

        try {
            $this->assertSame(self::TENANT_ID, $parsed->claims()->get('tenant_id'));
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function it_can_sign_in_anonymously(): void
    {
        $user = $this->auth->createAnonymousUser();
        $result = $this->auth->signInAsUser($user);

        try {
            $this->assertSame(self::TENANT_ID, $result->firebaseTenantId());
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testItCanSignInWithACustomToken(): void
    {
        $user = $this->auth->createAnonymousUser();
        $result = $this->auth->signInAsUser($user);

        try {
            $this->assertSame(self::TENANT_ID, $result->firebaseTenantId());
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }
}
