<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Request;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Tests\IntegrationTestCase;

class CreateUserTest extends IntegrationTestCase
{
    /**
     * @var Auth
     */
    private $auth;

    protected function setUp()
    {
        $this->auth = self::$firebase->getAuth();
    }

    public function testCreateUser()
    {
        $request = CreateUser::new()
            ->withUid($uid = bin2hex(random_bytes(5)))
            ->withDisplayName($displayName = 'Some display name')
            ->withPhotoUrl($photoUrl = 'https://example.org/photo.jpg')
            ->withClearTextPassword('secret')
            ->withPhoneNumber($phoneNumber = '+1234567'.random_int(1000, 9999))
            ->withVerifiedEmail($email = $uid.'@example.org');

        $user = $this->auth->createUser($request);

        $this->assertSame($uid, $user->uid);
        $this->assertSame($displayName, $user->displayName);
        $this->assertSame($photoUrl, $user->photoUrl); // Firebase stores the photo url in the email provider info
        $this->assertNotNull($user->passwordHash);
        $this->assertSame($phoneNumber, $user->phoneNumber);
        $this->assertSame($email, $user->email);
        $this->assertTrue($user->emailVerified);
        $this->assertFalse($user->disabled);

        $this->auth->deleteUser($user->uid);
    }
}
