<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class SignInResultTest extends UnitTestCase
{
    /**
     * @dataProvider fullResponse
     *
     * @param array<string, mixed> $input
     */
    public function testItCanBeCreated(array $input): void
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
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function fullResponse(): array
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
}
