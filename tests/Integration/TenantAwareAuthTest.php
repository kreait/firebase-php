<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Lcobucci\JWT\Token\Plain;

/**
 * @internal
 */
class TenantAwareAuthTest extends IntegrationTestCase
{
    /** @var Auth */
    private $auth;

    /** @var string */
    private $tenant = 'FirstTenant-fqqqc';

    protected function setUp(): void
    {
        $this->auth = self::$factory->withTenantId($this->tenant)->createAuth();
    }

    /**
     * @test
     */
    public function new_users_are_scoped_to_a_tenant(): void
    {
        $user = $this->auth->createUserWithEmailAndPassword(
            \uniqid(__FUNCTION__, false).'@domain.tld',
            'password123'
        );

        try {
            $this->assertSame($this->tenant, $user->tenantId);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    /**
     * @test
     */
    public function custom_tokens_include_the_tenant(): void
    {
        $token = $this->auth->createCustomToken('some-uid');

        $this->assertInstanceOf(Plain::class, $token);
        $this->assertSame($this->tenant, $token->claims()->get('tenant_id'));
    }

    public function it_can_sign_in_anonymously(): void
    {
        $result = $this->auth->signInAnonymously();

        try {
            $this->assertSame($this->tenant, $result->firebaseTenantId());
            $this->auth->verifyIdToken($result->idToken());
        } finally {
            $this->auth->deleteUser($result->firebaseUserId());
        }
    }

    /**
     * @test
     */
    public function it_can_sign_in_with_a_custom_token(): void
    {
        $user = $this->auth->createAnonymousUser();
        $result = $this->auth->signInAsUser($user);

        try {
            $this->assertSame($this->tenant, $result->firebaseTenantId());
            $this->auth->verifyIdToken($result->idToken());
        } finally {
            $this->auth->deleteUser($result->firebaseUserId());
        }
    }
}
