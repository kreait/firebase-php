<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Request;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Request\UpdateUser;
use Kreait\Firebase\Tests\IntegrationTestCase;

class UpdateUserTest extends IntegrationTestCase
{
    /**
     * @var Auth
     */
    private $auth;

    protected function setUp()
    {
        $this->auth = self::$firebase->getAuth();
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
                ->withUid($uid = bin2hex(random_bytes(5)))
        );

        $this->assertNotTrue($user->emailVerified);
        $this->assertNull($user->email);

        $updatedUser = $this->auth->updateUser($uid, UpdateUser::new()->markEmailAsVerified());

        $this->assertSame($user->uid, $updatedUser->uid);
        $this->assertNull($updatedUser->email);
        $this->assertTrue($updatedUser->emailVerified);

        $this->auth->deleteUser($updatedUser->uid);
    }

    public function testMarkExistingUnverifiedEmailAsVerified()
    {
        $user = $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = bin2hex(random_bytes(5)))
                ->withUnverifiedEmail($email = $uid.'@example.org')
        );

        $this->assertFalse($user->emailVerified);

        $updatedUser = $this->auth->updateUser($uid, UpdateUser::new()->markEmailAsVerified());

        $this->assertSame($user->uid, $updatedUser->uid);
        $this->assertSame($user->email, $updatedUser->email);
        $this->assertTrue($updatedUser->emailVerified);

        $this->auth->deleteUser($updatedUser->uid);
    }

    public function testMarkExistingVerifiedEmailAsUnverified()
    {
        $user = $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = bin2hex(random_bytes(5)))
                ->withVerifiedEmail($email = $uid.'@example.org')
        );

        $this->assertTrue($user->emailVerified);

        $updatedUser = $this->auth->updateUser($uid, UpdateUser::new()->markEmailAsUnverified());

        $this->assertSame($user->uid, $updatedUser->uid);
        $this->assertSame($user->email, $updatedUser->email);
        $this->assertFalse($updatedUser->emailVerified);

        $this->auth->deleteUser($updatedUser->uid);
    }
}
