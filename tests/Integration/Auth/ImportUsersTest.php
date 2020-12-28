<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Auth;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\ImportUserRecord;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 */
class ImportUsersTest extends IntegrationTestCase
{
    /** @var Auth */
    private $auth;

    protected function setUp(): void
    {
        $this->auth = self::$factory->createAuth();
    }

    public function testImportUsers(): void
    {
        $importResult = $this->auth->importUsers(
            [
                ImportUserRecord::new()
                    ->withUid($uid = \bin2hex(\random_bytes(5)))
                    ->withDisplayName($displayName = 'Some display name')
                    ->withPhotoUrl($photoUrl = 'https://example.org/photo.jpg')
                    ->withPhoneNumber($phoneNumber = '+1234567'.\random_int(1000, 9999))
                    ->withVerifiedEmail($email = $uid.'@example.org')
                    ->withCustomClaims($claims = ['admin' => true]),
            ]
        );

        $this->assertEquals(1, $importResult->getSuccessCount());

        $user = $this->auth->getUser($uid);

        $this->assertSame($uid, $user->uid);
        $this->assertSame($displayName, $user->displayName);
        $this->assertSame($photoUrl, $user->photoUrl); // Firebase stores the photo url in the email provider info
        $this->assertSame($phoneNumber, $user->phoneNumber);
        $this->assertSame($email, $user->email);
        $this->assertTrue($user->emailVerified);
        $this->assertEquals($claims, $user->customClaims);
        $this->assertFalse($user->disabled);

        $this->auth->deleteUser($user->uid);
    }

    public function testImportedUserReplacesExistingUser(): void
    {
        $this->auth->createUser(
            CreateUser::new()
                ->withUid($uid = \bin2hex(\random_bytes(5)))
                ->withVerifiedEmail($email = $uid.'@example.org')
                ->withPhoneNumber('+1234567'.\random_int(1000, 9999))
                ->withPhotoUrl('https://example.org/old-photo.jpg')
                ->withDisplayName('Old display name')
        );

        $importResult = $this->auth->importUsers(
            [
                ImportUserRecord::new()
                    ->withUid($uid)
                    ->withDisplayName($newDisplayName = 'Some display name')
                    ->withPhotoUrl($newPhotoUrl = 'https://example.org/photo.jpg')
                    ->withVerifiedEmail($email)
                    ->markAsDisabled(),
            ]
        );

        $this->assertEquals(1, $importResult->getSuccessCount());

        $user = $this->auth->getUser($uid);

        $this->assertSame($uid, $user->uid);
        $this->assertSame($newDisplayName, $user->displayName);
        $this->assertSame($newPhotoUrl, $user->photoUrl);
        $this->assertSame($email, $user->email);
        $this->assertTrue($user->emailVerified);
        $this->assertTrue($user->disabled);

        // Values that are not provided with import request fallback to default state
        $this->assertNull($user->phoneNumber);

        $this->auth->deleteUser($user->uid);
    }
}
