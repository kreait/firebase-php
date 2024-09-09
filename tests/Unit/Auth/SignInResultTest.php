<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Iterator;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class SignInResultTest extends UnitTestCase
{
    /**
     * @param array<string, mixed> $input
     */
    #[DataProvider('fullResponse')]
    #[Test]
    public function itCanBeCreated(array $input): void
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

    #[Test]
    public function itUsesTheLocalIdWhenTheFirebaseUidIsNotPresent(): void
    {
        $result = SignInResult::fromData(['localId' => 'some-id']);

        $this->assertSame('some-id', $result->firebaseUserId());
    }

    public static function fullResponse(): Iterator
    {
        yield 'snake_cased' => [[
            'idToken' => 'idToken',
            'refreshToken' => 'refreshToken',
            'accessToken' => 'accessToken',
            'expiresIn' => 3600,
        ]];
        yield 'camel_cased' => [[
            'id_token' => 'idToken',
            'refresh_token' => 'refreshToken',
            'access_token' => 'accessToken',
            'expires_in' => 3600,
        ]];
    }
}
