<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Request;

use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Value\ClearTextPassword;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CreateUserTest extends TestCase
{
    #[Test]
    public function createNew(): void
    {
        $request = CreateUser::new();
        $this->assertEmpty($request->jsonSerialize());
    }

    /**
     * @param array<array<string|mixed>> $properties
     * @param array<array<string|mixed>> $expected
     */
    #[DataProvider('propertiesProvider')]
    #[Test]
    public function withProperties(array $properties, array $expected): void
    {
        $request = CreateUser::withProperties($properties);

        $this->assertEqualsCanonicalizing($expected, $request->jsonSerialize());
    }

    public static function propertiesProvider(): \Iterator
    {
        $given = ['uid' => 'some-uid'];
        $expected = ['localId' => 'some-uid'];
        yield 'email' => [
            $given + ['email' => 'user@example.com'],
            $expected + ['email' => 'user@example.com'],
        ];
        yield 'unverified_email_through_flag' => [
            $given + ['email' => 'user@example.com', 'emailVerified' => false],
            $expected + ['email' => 'user@example.com', 'emailVerified' => false],
        ];
        yield 'unverified_email' => [
            $given + ['unverifiedEmail' => 'user@example.com'],
            $expected + ['email' => 'user@example.com', 'emailVerified' => false],
        ];
        yield 'verified_email_through_flag' => [
            $given + ['email' => 'user@example.com', 'emailVerified' => true],
            $expected + ['email' => 'user@example.com', 'emailVerified' => true],
        ];
        yield 'verified_email' => [
            $given + ['verifiedEmail' => 'user@example.com'],
            $expected + ['email' => 'user@example.com', 'emailVerified' => true],
        ];
        yield 'no_email_but_verification_flag' => [
            $given + ['emailVerified' => true],
            $expected + ['emailVerified' => true],
        ];
        yield 'disabled_1' => [
            $given + ['disabled' => true],
            $expected + ['disableUser' => true],
        ];
        yield 'disabled_2' => [
            $given + ['isDisabled' => true],
            $expected + ['disableUser' => true],
        ];
        yield 'disabled_3' => [
            $given + ['isDisabled' => null],
            $expected,
        ];
        yield 'disableUser_1' => [
            $given + ['disableUser' => true],
            $expected + ['disableUser' => true],
        ];
        yield 'disableUser_2' => [
            $given + ['disableUser' => false],
            $expected + ['disableUser' => false],
        ];
        yield 'explicitly_enabled_user_1' => [
            $given + ['enabled' => true],
            $expected + ['disableUser' => false],
        ];
        yield 'explicitly_enabled_user_2' => [
            $given + ['isEnabled' => true],
            $expected + ['disableUser' => false],
        ];
        yield 'explicitly_disabled_user_1' => [
            $given + ['enabled' => false],
            $expected + ['disableUser' => true],
        ];
        yield 'enableUser' => [
            $given + ['enableUser' => true],
            $expected + ['disableUser' => false],
        ];
        yield 'displayName' => [
            $given + ['displayName' => 'Äöüß Èâç!'],
            $expected + ['displayName' => 'Äöüß Èâç!'],
        ];
        yield 'phone' => [
            $given + ['phone' => '+123456789'],
            $expected + ['phoneNumber' => '+123456789'],
        ];
        yield 'phoneNumber' => [
            $given + ['phoneNumber' => '+123456789'],
            $expected + ['phoneNumber' => '+123456789'],
        ];
        yield 'photo' => [
            $given + ['photo' => 'https://example.com/photo.jpg'],
            $expected + ['photoUrl' => 'https://example.com/photo.jpg'],
        ];
        yield 'photoUrl' => [
            $given + ['photoUrl' => 'https://example.com/photo.jpg'],
            $expected + ['photoUrl' => 'https://example.com/photo.jpg'],
        ];
        yield 'password' => [
            $given + ['password' => 'secret'],
            $expected + ['password' => 'secret'],
        ];
        yield 'clearTextPassword' => [
            $given + ['clearTextPassword' => 'secret'],
            $expected + ['password' => 'secret'],
        ];
        yield 'clearTextPasswordObject' => [
            $given + ['clearTextPassword' => ClearTextPassword::fromString('secret')->value],
            $expected + ['password' => 'secret'],
        ];
    }
}
