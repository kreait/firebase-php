<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Contract\Auth;

/**
 * @internal
 *
 * @group auth-emulator
 * @group emulator
 */
final class TenantAwareAuthTest extends AuthTestCase
{
    protected function setUp(): void
    {
        $this->auth = self::$factory->withTenantId(self::$tenantId)->createAuth();
    }

    public function testNewUsersAreScopedToATenant(): void
    {
        $user = $this->auth->createUserWithEmailAndPassword(
            self::randomEmail(__FUNCTION__),
            'password123',
        );

        try {
            $this->assertSame(self::$tenantId, $user->tenantId);
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
            $this->assertSame(self::$tenantId, $parsed->claims()->get('tenant_id'));
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function it_can_sign_in_anonymously(): void
    {
        $user = $this->auth->createAnonymousUser();
        $result = $this->auth->signInAsUser($user);

        try {
            $this->assertSame(self::$tenantId, $result->firebaseTenantId());
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testItCanSignInWithACustomToken(): void
    {
        $user = $this->auth->createAnonymousUser();
        $result = $this->auth->signInAsUser($user);

        try {
            $this->assertSame(self::$tenantId, $result->firebaseTenantId());
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }
}
