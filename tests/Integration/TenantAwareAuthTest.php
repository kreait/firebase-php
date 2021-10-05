<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Lcobucci\JWT\Token\Plain;

/**
 * @internal
 */
final class TenantAwareAuthTest extends IntegrationTestCase
{
    private Auth $auth;

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
        $token = $this->auth->createCustomToken('some-uid');

        $this->assertInstanceOf(Plain::class, $token);
        $this->assertSame(self::TENANT_ID, $token->claims()->get('tenant_id'));
    }

    public function it_can_sign_in_anonymously(): void
    {
        $result = $this->auth->signInAnonymously();

        try {
            $this->assertSame(self::TENANT_ID, $result->firebaseTenantId());
            $this->auth->verifyIdToken($result->idToken());
        } finally {
            $this->auth->deleteUser($result->firebaseUserId());
        }
    }

    public function testItCanSignInWithACustomToken(): void
    {
        $user = $this->auth->createAnonymousUser();
        $result = $this->auth->signInAsUser($user);

        try {
            $this->assertSame(self::TENANT_ID, $result->firebaseTenantId());
            $this->auth->verifyIdToken($result->idToken());
        } finally {
            $this->auth->deleteUser($result->firebaseUserId());
        }
    }
}
