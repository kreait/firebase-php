<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Tests\UnitTestCase;
use Lcobucci\JWT\Builder;

/**
 * @internal
 */
final class SignInResultTest extends UnitTestCase
{
    /**
     * @test
     * @dataProvider fullResponse
     */
    public function it_can_be_created(array $input): void
    {
        $result = SignInResult::fromData($input);

        $this->assertSame($input, $result->data());

        $this->assertSame('idToken', $result->idToken());
        $this->assertSame('refreshToken', $result->refreshToken());
        $this->assertSame('accessToken', $result->accessToken());
        $this->assertSame(3600, $result->ttl());

        $this->assertSame([
            'token_type' => 'Bearer',
            'access_token' => 'accessToken',
            'id_token' => 'idToken',
            'refresh_token' => 'refreshToken',
            'expires_in' => 3600,
        ], $result->asTokenResponse());
    }

    /**
     * @test
     * @dataProvider fullResponseWithUserIdInIdToken
     */
    public function it_returns_a_user_id($uid, array $input): void
    {
        $this->assertSame($uid, SignInResult::fromData($input)->firebaseUserId());
    }

    public function fullResponse()
    {
        return [
            'snake_cased' => [[
                'idToken' => 'idToken',
                'refreshToken' => 'refreshToken',
                'accessToken' => 'accessToken',
                'expiresIn' => 3600,
            ]],
            'camel_cased' => [[
                'id_token' => 'idToken',
                'refresh_token' => 'refreshToken',
                'access_token' => 'accessToken',
                'expires_in' => 3600,
            ]],
        ];
    }

    public function fullResponseWithUserIdInIdToken()
    {
        $uid = 'firebase_user_id';

        return [
            'sub' => [
                $uid,
                [
                    'idToken' => (string) (new Builder())->withClaim('sub', $uid)->getToken(),
                ],
            ],
            'localId' => [
                $uid,
                [
                    'idToken' => (string) (new Builder())->withClaim('localId', $uid)->getToken(),
                ],
            ],
            'user_id' => [
                $uid,
                [
                    'idToken' => (string) (new Builder())->withClaim('user_id', $uid)->getToken(),
                ],
            ],
            'none' => [
                null,
                [
                    'idToken' => (string) (new Builder())->getToken(),
                ],
            ],
        ];
    }
}
