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

    /**
     * @return array<string, array<array<string|mixed>>>
     */
    public static function propertiesProvider(): array
    {
        $given = ['uid' => 'some-uid'];
        $expected = ['localId' => 'some-uid'];

        return [
            'email' => [
                $given + ['email' => 'user@example.com'],
                $expected + ['email' => 'user@example.com'],
            ],
            'unverified_email_through_flag' => [
                $given + ['email' => 'user@example.com', 'emailVerified' => false],
                $expected + ['email' => 'user@example.com', 'emailVerified' => false],
            ],
            'unverified_email' => [
                $given + ['unverifiedEmail' => 'user@example.com'],
                $expected + ['email' => 'user@example.com', 'emailVerified' => false],
            ],
            'verified_email_through_flag' => [
                $given + ['email' => 'user@example.com', 'emailVerified' => true],
                $expected + ['email' => 'user@example.com', 'emailVerified' => true],
            ],
            'verified_email' => [
                $given + ['verifiedEmail' => 'user@example.com'],
                $expected + ['email' => 'user@example.com', 'emailVerified' => true],
            ],
            'no_email_but_verification_flag' => [
                $given + ['emailVerified' => true],
                $expected + ['emailVerified' => true],
            ],
            'disabled_1' => [
                $given + ['disabled' => true],
                $expected + ['disableUser' => true],
            ],
            'disabled_2' => [
                $given + ['isDisabled' => true],
                $expected + ['disableUser' => true],
            ],
            'disabled_3' => [
                $given + ['isDisabled' => null],
                $expected,
            ],
            'disableUser_1' => [
                $given + ['disableUser' => true],
                $expected + ['disableUser' => true],
            ],
            'disableUser_2' => [
                $given + ['disableUser' => false],
                $expected + ['disableUser' => false],
            ],
            'explicitly_enabled_user_1' => [
                $given + ['enabled' => true],
                $expected + ['disableUser' => false],
            ],
            'explicitly_enabled_user_2' => [
                $given + ['isEnabled' => true],
                $expected + ['disableUser' => false],
            ],
            'explicitly_disabled_user_1' => [
                $given + ['enabled' => false],
                $expected + ['disableUser' => true],
            ],
            'enableUser' => [
                $given + ['enableUser' => true],
                $expected + ['disableUser' => false],
            ],
            'displayName' => [
                $given + ['displayName' => 'Äöüß Èâç!'],
                $expected + ['displayName' => 'Äöüß Èâç!'],
            ],
            'phone' => [
                $given + ['phone' => '+123456789'],
                $expected + ['phoneNumber' => '+123456789'],
            ],
            'phoneNumber' => [
                $given + ['phoneNumber' => '+123456789'],
                $expected + ['phoneNumber' => '+123456789'],
            ],
            'photo' => [
                $given + ['photo' => 'https://example.com/photo.jpg'],
                $expected + ['photoUrl' => 'https://example.com/photo.jpg'],
            ],
            'photoUrl' => [
                $given + ['photoUrl' => 'https://example.com/photo.jpg'],
                $expected + ['photoUrl' => 'https://example.com/photo.jpg'],
            ],
            'password' => [
                $given + ['password' => 'secret'],
                $expected + ['password' => 'secret'],
            ],
            'clearTextPassword' => [
                $given + ['clearTextPassword' => 'secret'],
                $expected + ['password' => 'secret'],
            ],
            'clearTextPasswordObject' => [
                $given + ['clearTextPassword' => ClearTextPassword::fromString('secret')->value],
                $expected + ['password' => 'secret'],
            ],
        ];
    }
}
