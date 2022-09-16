<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Request;

use DateTimeImmutable;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Request\UpdateUser;
use Kreait\Firebase\Tests\IntegrationTestCase;

use function bin2hex;
use function random_bytes;
use function random_int;

/**
 * @internal
 */
final class UpdateUserTest extends IntegrationTestCase
{
    private Auth $auth;

    protected function setUp(): void
    {
        $this->auth = self::$factory->createAuth();
    }

    public function testRemovePhotoUrl(): void
    {
        $photoUrl = 'http://example.com/a_photo.jpg';

        $user = $this->auth->createUser(CreateUser::new()->withPhotoUrl($photoUrl));
        self::assertSame($user->photoUrl, $photoUrl);

        $updatedUser = $this->auth->updateUser($user->uid, UpdateUser::new()->withRemovedPhotoUrl());

        self::assertNull($updatedUser->photoUrl);

        $this->auth->deleteUser($user->uid);
    }

    public function testRemoveDisplayName(): void
    {
        $displayName = 'A display name';

        $user = $this->auth->createUser(CreateUser::new()->withDisplayName($displayName));
        self::assertSame($user->displayName, $displayName);

        $updatedUser = $this->auth->updateUser($user->uid, UpdateUser::new()->withRemovedDisplayName());

        self::assertNull($updatedUser->displayName);

        $this->auth->deleteUser($user->uid);
    }

    public function testMarkNonExistingEmailAsVerified(): void
    {
        $user = $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = bin2hex(random_bytes(5))),
        );

        self::assertNotTrue($user->emailVerified);
        self::assertNull($user->email);

        $updatedUser = $this->auth->updateUser($uid, UpdateUser::new()->markEmailAsVerified());

        self::assertSame($user->uid, $updatedUser->uid);
        self::assertNull($updatedUser->email);
        self::assertTrue($updatedUser->emailVerified);

        $this->auth->deleteUser($updatedUser->uid);
    }

    public function testMarkExistingUnverifiedEmailAsVerified(): void
    {
        $user = $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = bin2hex(random_bytes(5)))
                ->withUnverifiedEmail($uid . '@example.org'),
        );

        self::assertFalse($user->emailVerified);

        $updatedUser = $this->auth->updateUser($user->uid, UpdateUser::new()->markEmailAsVerified());

        self::assertSame($user->uid, $updatedUser->uid);
        self::assertSame($user->email, $updatedUser->email);
        self::assertTrue($updatedUser->emailVerified);

        $this->auth->deleteUser($updatedUser->uid);
    }

    public function testMarkExistingVerifiedEmailAsUnverified(): void
    {
        $user = $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = bin2hex(random_bytes(5)))
                ->withVerifiedEmail($uid . '@example.org'),
        );

        self::assertTrue($user->emailVerified);

        $updatedUser = $this->auth->updateUser($uid, UpdateUser::new()->markEmailAsUnverified());

        self::assertSame($user->uid, $updatedUser->uid);
        self::assertSame($user->email, $updatedUser->email);
        self::assertFalse($updatedUser->emailVerified);

        $this->auth->deleteUser($updatedUser->uid);
    }

    public function testUpdateUserWithCustomAttributes(): void
    {
        $request = CreateUser::new()
            ->withUid($uid = bin2hex(random_bytes(5)));

        $this->auth->createUser($request);

        $request = UpdateUser::new()
            ->withCustomAttributes($claims = [
                'admin' => true,
                'groupId' => '1234',
            ]);

        $user = $this->auth->updateUser($uid, $request);
        self::assertEquals($claims, $user->customClaims);

        $idToken = $this->auth->signInAsUser($user)->idToken();
        self::assertNotNull($idToken);

        $verifiedToken = $this->auth->verifyIdToken($idToken);

        self::assertTrue($verifiedToken->claims()->get('admin'));
        self::assertSame('1234', $verifiedToken->claims()->get('groupId'));

        $this->auth->deleteUser($uid);
    }

    public function testRemovePhoneNumber(): void
    {
        $user = $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = bin2hex(random_bytes(5)))
                ->withVerifiedEmail($uid . '@example.org')
                ->withPhoneNumber($phoneNumber = '+1234567' . random_int(1000, 9999)),
        );

        self::assertSame($phoneNumber, $user->phoneNumber);

        $updatedUser = $this->auth->updateUser(
            $user->uid,
            UpdateUser::new()->withRemovedPhoneNumber(),
        );

        self::assertNull($updatedUser->phoneNumber);

        $this->auth->deleteUser($user->uid);
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/196
     */
    public function testReEnable(): void
    {
        $user = $this->auth->createUser([
            'disabled' => true,
        ]);

        $check = $this->auth->updateUser($user->uid, [
            'disabled' => false,
        ]);

        self::assertFalse($check->disabled);

        $this->auth->deleteUser($user->uid);
    }

    public function testTimeOfLastPasswordUpdateIsIncluded(): void
    {
        $user = $this->auth->createAnonymousUser();

        try {
            self::assertNull($user->metadata->passwordUpdatedAt);

            $updatedUser = $this->auth->updateUser($user->uid, ['password' => 'new-password']);

            self::assertInstanceOf(DateTimeImmutable::class, $updatedUser->metadata->passwordUpdatedAt);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }
}
