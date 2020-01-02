<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Request;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Request\UpdateUser;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 */
class UpdateUserTest extends IntegrationTestCase
{
    /**
     * @var Auth
     */
    private $auth;

    protected function setUp()
    {
        $this->auth = self::$factory->createAuth();
    }

    public function testRemovePhotoUrl()
    {
        $photoUrl = 'http://example.com/a_photo.jpg';

        $user = $this->auth->createUser(CreateUser::new()->withPhotoUrl($photoUrl));
        $this->assertSame($user->photoUrl, $photoUrl);

        $updatedUser = $this->auth->updateUser($user->uid, UpdateUser::new()->withRemovedPhotoUrl());

        $this->assertNull($updatedUser->photoUrl);

        $this->auth->deleteUser($user->uid);
    }

    public function testRemoveDisplayName()
    {
        $displayName = 'A display name';

        $user = $this->auth->createUser(CreateUser::new()->withDisplayName($displayName));
        $this->assertSame($user->displayName, $displayName);

        $updatedUser = $this->auth->updateUser($user->uid, UpdateUser::new()->withRemovedDisplayName());

        $this->assertNull($updatedUser->displayName);

        $this->auth->deleteUser($user->uid);
    }

    public function testMarkNonExistingEmailAsVerified()
    {
        $user = $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = \bin2hex(\random_bytes(5)))
        );

        $this->assertTrue($user->emailVerified !== true);
        $this->assertNull($user->email);

        $updatedUser = $this->auth->updateUser($uid, UpdateUser::new()->markEmailAsVerified());

        $this->assertSame($user->uid, $updatedUser->uid);
        $this->assertNull($updatedUser->email);
        $this->assertSame(true, $updatedUser->emailVerified);

        $this->auth->deleteUser($updatedUser->uid);
    }

    public function testMarkExistingUnverifiedEmailAsVerified()
    {
        $user = $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = \bin2hex(\random_bytes(5)))
                ->withUnverifiedEmail($email = $uid.'@example.org')
        );

        $this->assertSame(false, $user->emailVerified);

        $updatedUser = $this->auth->updateUser($uid, UpdateUser::new()->markEmailAsVerified());

        $this->assertSame($user->uid, $updatedUser->uid);
        $this->assertSame($user->email, $updatedUser->email);
        $this->assertSame(true, $updatedUser->emailVerified);

        $this->auth->deleteUser($updatedUser->uid);
    }

    public function testMarkExistingVerifiedEmailAsUnverified()
    {
        $user = $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = \bin2hex(\random_bytes(5)))
                ->withVerifiedEmail($email = $uid.'@example.org')
        );

        $this->assertSame(true, $user->emailVerified);

        $updatedUser = $this->auth->updateUser($uid, UpdateUser::new()->markEmailAsUnverified());

        $this->assertSame($user->uid, $updatedUser->uid);
        $this->assertSame($user->email, $updatedUser->email);
        $this->assertSame(false, $updatedUser->emailVerified);

        $this->auth->deleteUser($updatedUser->uid);
    }

    public function testUpdateUserWithCustomAttributes()
    {
        $request = CreateUser::new()
            ->withUid($uid = \bin2hex(\random_bytes(5)));

        $this->auth->createUser($request);

        $request = UpdateUser::new()
            ->withCustomAttributes($claims = [
                'admin' => true,
                'groupId' => '1234',
            ]);

        $user = $this->auth->updateUser($uid, $request);
        $this->assertEquals($claims, $user->customAttributes);

        $idToken = $this->auth->signInAsUser($user)->idToken();
        $this->assertNotNull($idToken);

        $verifiedToken = $this->auth->verifyIdToken($idToken);

        $this->assertTrue($verifiedToken->getClaim('admin'));
        $this->assertSame('1234', $verifiedToken->getClaim('groupId'));

        $this->auth->deleteUser($uid);
    }

    public function testRemovePhoneNumber()
    {
        $user = $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = \bin2hex(\random_bytes(5)))
                ->withVerifiedEmail($email = $uid.'@example.org')
                ->withPhoneNumber($phoneNumber = '+1234567'.\random_int(1000, 9999))
        );

        $this->assertSame($phoneNumber, $user->phoneNumber);

        $updatedUser = $this->auth->updateUser(
            $user->uid,
            UpdateUser::new()->withRemovedPhoneNumber()
        );

        $this->assertNull($updatedUser->phoneNumber);

        $this->auth->deleteUser($user->uid);
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/196
     */
    public function testReEnable()
    {
        $user = $this->auth->createUser([
            'disabled' => true,
        ]);

        $check = $this->auth->updateUser($user->uid, [
            'disabled' => false,
        ]);

        $this->assertFalse($check->disabled);

        $this->auth->deleteUser($user->uid);
    }
}
