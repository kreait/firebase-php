<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Request;

use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Value\ClearTextPassword;
use Kreait\Firebase\Value\PhoneNumber;
use PHPUnit\Framework\TestCase;

class CreateUserTest extends TestCase
{
    public function testCreateNew()
    {
        $request = CreateUser::new();

        $this->assertEquals([], json_decode(json_encode($request), true));
    }

    /**
     * @dataProvider propertiesProvider
     */
    public function testWithProperties(array $properties, array $expected)
    {
        $request = CreateUser::withProperties($properties);

        $this->assertEquals($expected, json_decode(json_encode($request), true));
    }

    public function propertiesProvider(): array
    {
        $given = ['uid' => 'some-uid'];
        $expected = ['localId' => 'some-uid'];

        return [
            'email' => [
                $given + ['email' => 'user@domain.tld'],
                $expected + ['email' => 'user@domain.tld'],
            ],
            'unverified_email_through_flag' => [
                $given + ['email' => 'user@domain.tld', 'emailVerified' => false],
                $expected + ['email' => 'user@domain.tld', 'emailVerified' => false],
            ],
            'unverified_email' => [
                $given + ['unverifiedEmail' => 'user@domain.tld'],
                $expected + ['email' => 'user@domain.tld', 'emailVerified' => false],
            ],
            'verified_email_through_flag' => [
                $given + ['email' => 'user@domain.tld', 'emailVerified' => true],
                $expected + ['email' => 'user@domain.tld', 'emailVerified' => true],
            ],
            'verified_email' => [
                $given + ['verifiedEmail' => 'user@domain.tld'],
                $expected + ['email' => 'user@domain.tld', 'emailVerified' => true],
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
            'disableUser' => [
                $given + ['disableUser' => true],
                $expected + ['disableUser' => true],
            ],
            'enableUser' => [
                $given + ['disableUser' => false],
                $expected + ['disableUser' => false],
            ],
            'explicitely_enabled_user_1' => [
                $given + ['enabled' => true],
                $expected + ['disableUser' => false],
            ],
            'explicitely_enabled_user_2' => [
                $given + ['isEnabled' => true],
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
            'phoneNumberObject' => [
                $given + ['phoneNumber' => new PhoneNumber('+123456789')],
                $expected + ['phoneNumber' => '+123456789'],
            ],
            'photo' => [
                $given + ['photo' => 'https://domain.tld/photo.jpg'],
                $expected + ['photoUrl' => 'https://domain.tld/photo.jpg'],
            ],
            'photoUrl' => [
                $given + ['photoUrl' => 'https://domain.tld/photo.jpg'],
                $expected + ['photoUrl' => 'https://domain.tld/photo.jpg'],
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
                $given + ['clearTextPassword' => new ClearTextPassword('secret')],
                $expected + ['password' => 'secret'],
            ],
        ];
    }
}
