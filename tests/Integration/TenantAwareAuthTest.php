<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
#[Group('emulator')]
final class TenantAwareAuthTest extends AuthTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (self::$tenantId === null) {
            $this->markTestSkipped('Tenant aware tests require a tenant ID');
        }

        $this->auth = self::$factory
            ->withTenantId(self::$tenantId)
            ->createAuth()
        ;
    }

    #[Test]
    public function newUsersAreScopedToATenant(): void
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

    #[Test]
    public function customTokensIncludeTheTenant(): void
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

    #[Test]
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

    #[Test]
    public function itCanSignInWithACustomToken(): void
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
