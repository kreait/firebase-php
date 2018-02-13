<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Tests\IntegrationTestCase;

class AuthTest extends IntegrationTestCase
{
    /**
     * @var Auth
     */
    private $auth;

    protected function setUp()
    {
        $this->auth = self::$firebase->getAuth();
    }

    public function testCreateAnonymousUser()
    {
        $user = $this->auth->createAnonymousUser();

        $this->assertNull($user->getEmail());

        $this->auth->deleteUser($user);
    }

    public function testCreateUserWithEmailAndPassword()
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $email = uniqid('').'@domain.tld';
        $password = 'foobar';

        $this->auth->createUserWithEmailAndPassword($email, $password);
        $user = $this->auth->getUserByEmailAndPassword($email, $password);

        $this->assertSame($email, $user->getEmail());
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertNotEmpty($user->getRefreshToken());

        $this->auth->deleteUser($user);
    }

    public function testChangeUserPassword()
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $email = uniqid('').'@domain.tld';

        $user = $this->auth->createUserWithEmailAndPassword($email, 'old password');

        $this->auth->changeUserPassword($user, 'new password');

        $refetchedUser = $this->auth->getUserByEmailAndPassword($email, 'new password');

        $this->auth->deleteUser($refetchedUser);

        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testChangeUserEmail()
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $uniqid = uniqid('');
        $email = "${uniqid}@domain.tld";
        $newEmail = "${uniqid}-changed@domain.tld";
        $password = 'my password';

        $user = $this->auth->createUserWithEmailAndPassword($email, $password);

        $this->auth->changeUserEmail($user, $newEmail);

        $refetchedUser = $this->auth->getUserByEmailAndPassword($newEmail, $password);

        $this->auth->deleteUser($refetchedUser);

        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testSendEmailVerification()
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $uniqid = uniqid();
        $email = "${uniqid}@domain.tld";
        $password = 'my password';

        $user = $this->auth->createUserWithEmailAndPassword($email, $password);

        $this->auth->sendEmailVerification($user);

        $this->auth->deleteUser($user);

        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testSendPasswordResetEmail()
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $uniqid = uniqid();
        $email = "${uniqid}@domain.tld";
        $password = 'my password';

        $user = $this->auth->createUserWithEmailAndPassword($email, $password);

        $this->auth->sendPasswordResetEmail($user);
        $this->auth->sendPasswordResetEmail($user->getEmail());

        $this->auth->deleteUser($user);

        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testListUsers()
    {
        // We already should have a list of users, but let's add another one,
        // just to be sure
        $createdUsers = [
            $this->auth->createAnonymousUser(),
            $this->auth->createAnonymousUser(),
        ];

        $users = $this->auth->listUsers($maxResults = 2, $batchSize = 1);

        $count = 0;
        foreach ($users as $userData) {
            $this->assertInternalType('array', $userData);
            $this->assertArrayHasKey('localId', $userData);
            ++$count;
        }

        $this->assertSame($maxResults, $count);

        foreach ($createdUsers as $createdUser) {
            $this->auth->deleteUser($createdUser);
        }

        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testVerifyIdToken()
    {
        $user = $this->auth->createAnonymousUser();

        $idToken = $user->getIdToken();

        $this->auth->verifyIdToken($idToken);

        $this->auth->deleteUser($user);

        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testRevokeRefreshTokens()
    {
        $user = $this->auth->createAnonymousUser();

        $idToken = $user->getIdToken();

        $this->auth->verifyIdToken($idToken, $checkIfRevoked = false); // Should not throw an exception

        $this->auth->revokeRefreshTokens($user->getUid());

        $this->expectException(RevokedIdToken::class);

        $this->auth->verifyIdToken($idToken, $checkIfRevoked = true);

        $this->auth->deleteUser($user);
    }

    public function testVerifyIdTokenString()
    {
        $user = $this->auth->createAnonymousUser();

        $idToken = $user->getIdToken();

        $this->auth->verifyIdToken((string) $idToken);

        $this->auth->deleteUser($user);

        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testDisableAndEnableUser()
    {
        $user = $this->auth->createAnonymousUser();

        $this->auth->disableUser($user);

        $this->assertTrue($this->auth->getUserInfo($user->getUid())['disabled']);

        $this->auth->enableUser($user);

        $this->assertFalse($this->auth->getUserInfo($user->getUid())['disabled']);

        $this->auth->deleteUser($user);
    }

    public function testGetUserInfo()
    {
        $user = $this->auth->createAnonymousUser();

        $userInfo = $this->auth->getUserInfo($user->getUid());

        $this->assertArrayHasKey('localId', $userInfo);

        $this->auth->deleteUser($user);
    }

    public function testGetUserRecord()
    {
        $user = $this->auth->createAnonymousUser();

        $userRecord = $this->auth->getUserRecord($user->getUid());

        $this->assertSame($user->getUid(), $userRecord->uid);

        $this->auth->deleteUser($user);
    }
}
