<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Request;

use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Tests\IntegrationTestCase;

use function bin2hex;
use function random_bytes;
use function random_int;

/**
 * @internal
 */
final class CreateUserTest extends IntegrationTestCase
{
    private Auth $auth;

    protected function setUp(): void
    {
        $this->auth = self::$factory->createAuth();
    }

    public function testCreateUser(): void
    {
        $request = CreateUser::new()
            ->withUid($uid = bin2hex(random_bytes(5)))
            ->withDisplayName($displayName = 'Some display name')
            ->withPhotoUrl($photoUrl = 'https://example.org/photo.jpg')
            ->withClearTextPassword('secret')
            ->withPhoneNumber($phoneNumber = '+1234567' . random_int(1000, 9999))
            ->withVerifiedEmail($email = $uid . '@example.org');

        $user = $this->auth->createUser($request);

        self::assertSame($uid, $user->uid);
        self::assertSame($displayName, $user->displayName);
        self::assertSame($photoUrl, $user->photoUrl); // Firebase stores the photo url in the email provider info
        self::assertNotNull($user->passwordHash);
        self::assertSame($phoneNumber, $user->phoneNumber);
        self::assertSame($email, $user->email);
        self::assertTrue($user->emailVerified);
        self::assertFalse($user->disabled);

        $this->auth->deleteUser($user->uid);
    }

    public function testCreateUserWithoutEmailButMarkTheEmailAsUnverified(): void
    {
        $request = CreateUser::new()
            ->withUid($uid = bin2hex(random_bytes(5)))
            ->markEmailAsUnverified();

        $user = $this->auth->createUser($request);

        self::assertSame($uid, $user->uid);
        self::assertNull($user->email);
        self::assertFalse($user->emailVerified);

        $this->auth->deleteUser($user->uid);
    }
}
