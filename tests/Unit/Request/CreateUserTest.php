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
        return [
            'rewrite_uid' => [
                ['uid' => 'uid'],
                ['localId' => 'uid'],
            ],
            'email_is_unverified_by_default' => [
                ['email' => 'user@domain.tld'],
                ['email' => 'user@domain.tld', 'emailVerified' => false],
            ],
            'unverified_email_through_flag' => [
                ['email' => 'user@domain.tld', 'emailVerified' => false],
                ['email' => 'user@domain.tld', 'emailVerified' => false],
            ],
            'unverified_email' => [
                ['unverifiedEmail' => 'user@domain.tld'],
                ['email' => 'user@domain.tld', 'emailVerified' => false],
            ],
            'verified_email_through_flag' => [
                ['email' => 'user@domain.tld', 'emailVerified' => true],
                ['email' => 'user@domain.tld', 'emailVerified' => true],
            ],
            'verified_email' => [
                ['verifiedEmail' => 'user@domain.tld'],
                ['email' => 'user@domain.tld', 'emailVerified' => true],
            ],
            'no_email_but_verification_flag' => [
                ['emailVerified' => true],
                [],
            ],
            'disabled_user_1' => [
                ['disabled' => true],
                ['disableUser' => true],
            ],
            'disabled_user_2' => [
                ['isDisabled' => true],
                ['disableUser' => true],
            ],
            'explicitely_enabled_user_1' => [
                ['enabled' => true],
                [],
            ],
            'explicitely_enabled_user_2' => [
                ['isEnabled' => true],
                [],
            ],
            'displayName' => [
                ['displayName' => 'Äöüß Èâç!'],
                ['displayName' => 'Äöüß Èâç!'],
            ],
            'phone' => [
                ['phone' => '+123456789'],
                ['phoneNumber' => '+123456789'],
            ],
            'phoneNumber' => [
                ['phoneNumber' => '+123456789'],
                ['phoneNumber' => '+123456789'],
            ],
            'phoneNumberObject' => [
                ['phoneNumber' => new PhoneNumber('+123456789')],
                ['phoneNumber' => '+123456789'],
            ],
            'photo' => [
                ['photo' => 'https://domain.tld/photo.jpg'],
                ['photoUrl' => 'https://domain.tld/photo.jpg'],
            ],
            'photoUrl' => [
                ['photoUrl' => 'https://domain.tld/photo.jpg'],
                ['photoUrl' => 'https://domain.tld/photo.jpg'],
            ],
            'password' => [
                ['password' => 'secret'],
                ['password' => 'secret'],
            ],
            'clearTextPassword' => [
                ['clearTextPassword' => 'secret'],
                ['password' => 'secret'],
            ],
            'clearTextPasswordObject' => [
                ['clearTextPassword' => new ClearTextPassword('secret')],
                ['password' => 'secret'],
            ],
        ];
    }
}
