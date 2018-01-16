<?php

declare(strict_types=1);

namespace Kreait\Tests\Firebase\Integration;

use Kreait\Firebase\Auth;

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

    public function testAnonymousUser()
    {
        $user = $this->auth->createAnonymousUser();

        $this->assertNull($user->getEmail());

        $this->auth->deleteUser($user);
    }

    public function testUserWithEmailAndPassword()
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

    public function testVerifyIdTokenString()
    {
        $user = $this->auth->createAnonymousUser();

        $idToken = $user->getIdToken();

        $this->auth->verifyIdToken((string) $idToken);

        $this->auth->deleteUser($user);

        $this->assertTrue($noExceptionHasBeenThrown = true);
    }
}
