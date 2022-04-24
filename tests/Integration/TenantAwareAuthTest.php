<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Auth\SignIn\FailedToSignIn;

/**
 * @internal
 *
 * @group emulator
 */
final class TenantAwareAuthTest extends AuthTestCase
{
    protected function setUp(): void
    {
        $this->auth = self::$factory->withTenantId(self::TENANT_ID)->createAuth();
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

    public function testSignInWithRefreshToken(): void
    {
        if (!$this->isEmulated()) {
            parent::testSignInWithRefreshToken();

            return;
        }

        // The Firebase Emulator is not yet able to sign in with refresh tokens
        // scoped to a tenant.
        // See https://github.com/firebase/firebase-js-sdk/issues/6125
        try {
            parent::testSignInWithRefreshToken();
            $this->fail('Signing in with a refresh token scoped to a tenant should not be working.');
        } catch (FailedToSignIn $e) {
            $this->assertSame('INVALID_REFRESH_TOKEN', $e->getMessage());
        }
    }
}
