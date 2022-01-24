<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Utils;
use Kreait\Firebase\Auth\CreateActionLink\FailedToCreateActionLink;
use Kreait\Firebase\Auth\CreateSessionCookie\FailedToCreateSessionCookie;
use Kreait\Firebase\Auth\SendActionLink\FailedToSendActionLink;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Tests\IntegrationTestCase;

abstract class AuthTestCase extends IntegrationTestCase
{
    /** @var Auth */
    protected $auth;

    public function testCreateAnonymousUser(): void
    {
        $user = $this->auth->createAnonymousUser();

        try {
            $this->assertNull($user->email);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testCreateUserWithEmailAndPassword(): void
    {
        $email = self::randomEmail(__FUNCTION__);
        $password = 'foobar';

        try {
            $check = $this->auth->createUserWithEmailAndPassword($email, $password);

            $this->assertSame($email, $check->email);
            $this->assertFalse($check->emailVerified);
        } finally {
            if (isset($check) && $check instanceof UserRecord) {
                $this->auth->deleteUser($check->uid);
            }
        }
    }

    public function testChangeUserPassword(): void
    {
        $email = self::randomEmail(__FUNCTION__);

        $user = $this->auth->createUserWithEmailAndPassword($email, 'old password');

        $this->auth->changeUserPassword($user->uid, 'new password');

        $this->auth->deleteUser($user->uid);
        $this->addToAssertionCount(1);
    }

    public function testChangeUserEmail(): void
    {
        $email = self::randomEmail(__FUNCTION__.'_1');
        $newEmail = self::randomEmail(__FUNCTION__.'_2');
        $password = 'my password';

        $user = $this->auth->createUserWithEmailAndPassword($email, $password);

        $check = $this->auth->changeUserEmail($user->uid, $newEmail);
        $this->assertSame($newEmail, $check->email);

        $refetchedUser = $this->auth->getUserByEmail($newEmail);
        $this->assertSame($newEmail, $refetchedUser->email);

        $this->auth->deleteUser($user->uid);
    }

    public function testGetEmailVerificationLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        try {
            $this->auth->getEmailVerificationLink((string) $user->email);
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testSendEmailVerificationLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        try {
            $this->auth->sendEmailVerificationLink((string) $user->email);
            // We can't test the reception, but if we don't get an error, we consider it working
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testSendEmailVerificationLinkToUnknownUser(): void
    {
        $this->expectException(FailedToSendActionLink::class);
        $this->auth->sendEmailVerificationLink(self::randomEmail(__FUNCTION__));
    }

    public function testSendEmailVerificationLinkToDisabledUser(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        try {
            $this->auth->disableUser($user->uid);

            $this->expectException(FailedToSendActionLink::class);
            $this->auth->sendEmailVerificationLink((string) $user->email);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testGetPasswordResetLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        try {
            $this->auth->getPasswordResetLink((string) $user->email);
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testSendPasswordResetLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        try {
            $this->auth->sendPasswordResetLink((string) $user->email);
            // We can't test the reception, but if we don't get an error, we consider it working
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testGetSignInWithEmailLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        \assert($user->email !== null);

        try {
            $this->auth->getSignInWithEmailLink($user->email);
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testSendSignInWithEmailLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        try {
            $this->auth->sendSignInWithEmailLink((string) $user->email);
            // We can't test the reception, but if we don't get an error, we consider it working
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testGetUnsupportedEmailActionLink(): void
    {
        $this->expectException(FailedToCreateActionLink::class);
        $this->auth->getEmailActionLink('unsupported', self::randomEmail(__FUNCTION__));
    }

    public function testGetLocalizedEmailActionLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        $this->assertIsString($user->email);

        $link = $this->auth->getEmailVerificationLink($user->email, null, 'fr');

        $this->assertStringContainsString('lang=fr', $link);
    }

    public function testSendUnsupportedEmailActionLink(): void
    {
        $this->expectException(FailedToSendActionLink::class);
        $this->auth->sendEmailActionLink('unsupported', self::randomEmail(__FUNCTION__));
    }

    public function testListUsers(): void
    {
        // We already should have a list of users, but let's add another one,
        // just to be sure
        $createdUsers = [
            $this->auth->createUser([]),
            $this->auth->createUser([]),
        ];

        $userRecords = $this->auth->listUsers($maxResults = 2, 1);

        $count = 0;
        foreach ($userRecords as $userData) {
            $this->assertInstanceOf(UserRecord::class, $userData);
            ++$count;
        }

        $this->assertSame($maxResults, $count);

        foreach ($createdUsers as $createdUser) {
            $this->auth->deleteUser($createdUser->uid);
        }
    }

    public function testVerifyIdToken(): void
    {
        $result = $this->auth->signInAnonymously();

        $uid = $result->firebaseUserId();
        $this->assertIsString($uid);

        try {
            $idToken = $result->idToken();
            $this->assertIsString($result->firebaseUserId());
            $this->assertIsString($idToken);

            $verifiedToken = $this->auth->verifyIdToken($idToken);

            $this->assertSame($uid, $verifiedToken->claims()->get('sub'));

            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    public function testRevokeRefreshTokens(): void
    {
        $idToken = $this->auth->signInAnonymously()->idToken();
        $this->assertIsString($idToken);

        $token = $this->auth->verifyIdToken($idToken, $checkIfRevoked = false);

        $uid = $token->claims()->get('sub');

        \sleep(1);
        $this->auth->revokeRefreshTokens($uid);

        $this->expectException(RevokedIdToken::class);

        try {
            $this->auth->verifyIdToken($idToken, true);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    public function testVerifyIdTokenString(): void
    {
        $result = $this->auth->signInAnonymously();

        $uid = $result->firebaseUserId();
        $this->assertIsString($uid);

        $idToken = $result->idToken();
        $this->assertIsString($idToken);

        try {
            $verifiedToken = $this->auth->verifyIdToken($idToken);
            $this->assertSame($uid, $verifiedToken->claims()->get('sub'));
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    public function testCreateSessionCookie(): void
    {
        $signInResult = $this->auth->signInAnonymously();
        /** @var string $uid */
        $uid = $signInResult->firebaseUserId();

        try {
            $idToken = $signInResult->idToken();
            $this->assertIsString($idToken);

            $sessionCookie = $this->auth->createSessionCookie($idToken, 3600);
            $this->assertIsString($sessionCookie);

            $parsed = $this->auth->parseToken($sessionCookie);

            $this->assertSame($uid, $parsed->claims()->get('sub'));
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    public function testCreateSessionCookieWithInvalidTTL(): void
    {
        $signInResult = $this->auth->signInAnonymously();
        /** @var string $uid */
        $uid = $signInResult->firebaseUserId();

        try {
            $idToken = $signInResult->idToken();
            $this->assertIsString($idToken);

            $this->expectException(\InvalidArgumentException::class);
            $this->auth->createSessionCookie($idToken, 5);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    public function testCreateSessionCookieWithInvalidIdToken(): void
    {
        $this->expectException(FailedToCreateSessionCookie::class);
        $this->expectExceptionMessageMatches('/INVALID_ID_TOKEN/');
        $this->auth->createSessionCookie('invalid', 3600);
    }

    public function testDisableAndEnableUser(): void
    {
        $user = $this->auth->createUser([]);

        $check = $this->auth->disableUser($user->uid);
        $this->assertTrue($check->disabled);

        $check = $this->auth->enableUser($user->uid);
        $this->assertFalse($check->disabled);

        $this->auth->deleteUser($user->uid);
    }

    public function testGetUser(): void
    {
        $user = $this->auth->createUser([]);

        $check = $this->auth->getUser($user->uid);

        $this->assertSame($user->uid, $check->uid);

        $this->auth->deleteUser($user->uid);
    }

    public function testGetUsers(): void
    {
        $one = $this->auth->createAnonymousUser();
        $two = $this->auth->createAnonymousUser();

        $check = $this->auth->getUsers([$one->uid, $two->uid, 'non_existing']);

        try {
            $this->assertInstanceOf(UserRecord::class, $check[$one->uid]);
            $this->assertInstanceOf(UserRecord::class, $check[$two->uid]);
            $this->assertNull($check['non_existing']);
        } finally {
            $this->auth->deleteUser($one->uid);
            $this->auth->deleteUser($two->uid);
        }
    }

    public function testGetNonExistingUser(): void
    {
        $user = $this->auth->createUser([]);
        $this->auth->deleteUser($user->uid);

        $this->expectException(UserNotFound::class);
        $this->auth->getUser($user->uid);
    }

    public function testGetUserByNonExistingEmail(): void
    {
        $user = $this->auth->createUser([
            'email' => $email = self::randomEmail(__FUNCTION__),
        ]);
        $this->auth->deleteUser($user->uid);

        $this->expectException(UserNotFound::class);
        $this->auth->getUserByEmail($email);
    }

    public function testGetUserByPhoneNumber(): void
    {
        $phoneNumber = '+1234567'.\random_int(1000, 9999);

        $user = $this->auth->createUser([
            'phoneNumber' => $phoneNumber,
        ]);

        $check = $this->auth->getUserByPhoneNumber($phoneNumber);

        $this->assertSame($user->uid, $check->uid);

        $this->auth->deleteUser($user->uid);
    }

    public function testGetUserByNonExistingPhoneNumber(): void
    {
        $phoneNumber = '+1234567'.\random_int(1000, 9999);

        $user = $this->auth->createUser([
            'phoneNumber' => $phoneNumber,
        ]);
        $this->auth->deleteUser($user->uid);

        $this->expectException(UserNotFound::class);
        $this->auth->getUserByPhoneNumber($phoneNumber);
    }

    public function testCreateUser(): void
    {
        $uid = \bin2hex(\random_bytes(5));
        $userRecord = $this->auth->createUser([
            'uid' => $uid,
            'displayName' => $displayName = self::randomString(__FUNCTION__),
            'verifiedEmail' => $email = self::randomEmail(__FUNCTION__),
        ]);

        $this->assertSame($uid, $userRecord->uid);
        $this->assertSame($displayName, $userRecord->displayName);
        $this->assertTrue($userRecord->emailVerified);
        $this->assertSame($email, $userRecord->email);

        $this->auth->deleteUser($uid);
    }

    public function testUpdateUserWithUidAsAdditionalArgument(): void
    {
        $user = $this->auth->createUser([]);
        $this->auth->updateUser($user->uid, []);
        $this->auth->deleteUser($user->uid);
        $this->addToAssertionCount(1);
    }

    public function testDeleteNonExistingUser(): void
    {
        $user = $this->auth->createUser([]);

        $this->auth->deleteUser($user->uid);

        $this->expectException(UserNotFound::class);
        $this->auth->deleteUser($user->uid);
    }

    public function testBatchDeleteDisabledUsers(): void
    {
        $enabledOne = $this->auth->createAnonymousUser();
        $enabledTwo = $this->auth->createAnonymousUser();

        $disabled = $this->auth->createAnonymousUser();
        $this->auth->updateUser($disabled->uid, ['disabled' => true]);

        $uids = [$enabledOne->uid, $disabled->uid, $enabledTwo->uid];

        $result = $this->auth->deleteUsers($uids, false);

        $this->assertSame(1, $result->successCount());
        $this->assertSame(2, $result->failureCount());
        $this->assertCount(2, $result->rawErrors());
    }

    public function testBatchForceDeleteUsers(): void
    {
        $enabledOne = $this->auth->createAnonymousUser();
        $enabledTwo = $this->auth->createAnonymousUser();

        $disabled = $this->auth->createAnonymousUser();
        $this->auth->updateUser($disabled->uid, ['disabled' => true]);

        $uids = [$enabledOne->uid, $disabled->uid, $enabledTwo->uid];

        $result = $this->auth->deleteUsers($uids, true);

        $this->assertSame(3, $result->successCount());
        $this->assertSame(0, $result->failureCount());
        $this->assertCount(0, $result->rawErrors());
    }

    public function testSetCustomUserClaims(): void
    {
        $user = $this->auth->createAnonymousUser();

        try {
            $this->auth->setCustomUserClaims($user->uid, $claims = ['a' => 'b']);

            $this->assertEquals($claims, $this->auth->getUser($user->uid)->customClaims);

            $this->auth->setCustomUserClaims($user->uid, null);

            $this->assertSame([], $this->auth->getUser($user->uid)->customClaims);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testUnlinkProvider(): void
    {
        $uid = self::randomString(__FUNCTION__);

        $user = $this->auth->createUser([
            'uid' => $uid,
            'verifiedEmail' => self::randomEmail($uid),
            'phone' => '+1234567'.\random_int(1000, 9999),
        ]);

        $updatedUser = $this->auth->unlinkProvider($user->uid, 'phone');

        $this->assertNull($updatedUser->phoneNumber);

        $this->auth->deleteUser($user->uid);
    }

    public function testVerifyPasswordResetCode(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        \assert(\is_string($user->email));

        try {
            $url = $this->auth->getPasswordResetLink($user->email);

            \parse_str((string) \parse_url($url, PHP_URL_QUERY), $query);

            $email = $this->auth->verifyPasswordResetCode($query['oobCode']);
            $this->assertSame($email, $user->email);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testVerifyPasswordWithInvalidOobCode(): void
    {
        $this->expectException(InvalidOobCode::class);
        $this->auth->verifyPasswordResetCode('invalid');
    }

    public function testConfirmPasswordReset(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        $url = $this->auth->getPasswordResetLink($user->email);

        \parse_str(\parse_url($url, PHP_URL_QUERY), $query);

        $email = $this->auth->confirmPasswordReset($query['oobCode'], 'newPassword123');

        try {
            $this->assertSame($email, $user->email);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testConfirmPasswordResetAndInvalidateRefreshTokens(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        \assert(\is_string($user->email));

        $url = $this->auth->getPasswordResetLink($user->email);

        $queryString = \parse_url($url, PHP_URL_QUERY);
        \assert(\is_string($queryString));

        \parse_str($queryString, $query);
        \assert(\is_array($query));

        $email = $this->auth->confirmPasswordReset($query['oobCode'], 'newPassword123', true);
        \sleep(1); // wait for a second

        try {
            $this->assertSame($email, $user->email);
            $this->assertGreaterThan($user->tokensValidAfterTime, $this->auth->getUser($user->uid)->tokensValidAfterTime);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testConfirmPasswordResetWithInvalidOobCode(): void
    {
        $this->expectException(InvalidOobCode::class);
        $this->auth->confirmPasswordReset('invalid', 'newPassword123');
    }

    public function testSignInAsUser(): void
    {
        $user = $this->auth->createAnonymousUser();

        $result = $this->auth->signInAsUser($user);

        $this->assertIsString($result->idToken());
        $this->assertNull($result->accessToken());
        $this->assertIsString($result->refreshToken());
        $this->assertIsString($result->firebaseUserId());

        $this->auth->deleteUser($user->uid);
    }

    public function testSignInWithCustomToken(): void
    {
        $user = $this->auth->createAnonymousUser();

        $customToken = $this->auth->createCustomToken($user->uid);

        $result = $this->auth->signInWithCustomToken($customToken);

        $this->assertIsString($result->idToken());
        $this->assertNull($result->accessToken());
        $this->assertIsString($result->refreshToken());
        $this->assertIsString($result->firebaseUserId());

        $this->auth->deleteUser($user->uid);
    }

    public function testSignInWithRefreshToken(): void
    {
        $user = $this->auth->createAnonymousUser();

        // We need to sign in once to get a refresh token
        $firstRefreshToken = $this->auth->signInAsUser($user)->refreshToken();
        $this->assertIsString($firstRefreshToken);

        $result = $this->auth->signInWithRefreshToken($firstRefreshToken);

        $this->assertIsString($result->idToken());
        $this->assertIsString($result->accessToken());
        $this->assertIsString($result->refreshToken());
        $this->assertIsString($result->firebaseUserId());

        $this->auth->deleteUser($user->uid);
    }

    public function testSignInWithEmailAndPassword(): void
    {
        $email = self::randomEmail(__FUNCTION__);
        $password = 'my-perfect-password';

        $user = $this->createUserWithEmailAndPassword($email, $password);

        $result = $this->auth->signInWithEmailAndPassword($email, $password);

        $this->assertIsString($result->idToken());
        $this->assertNull($result->accessToken());
        $this->assertIsString($result->refreshToken());
        $this->assertIsString($result->firebaseUserId());

        $this->auth->deleteUser($user->uid);
    }

    public function testSignInWithEmailAndOobCode(): void
    {
        $email = self::randomEmail(__FUNCTION__);
        $password = 'my-perfect-password';

        $user = $this->createUserWithEmailAndPassword($email, $password);

        $signInLink = $this->auth->getSignInWithEmailLink($email);
        $query = (string) \parse_url($signInLink, PHP_URL_QUERY);
        $oobCode = Query::parse($query)['oobCode'] ?? '';

        $result = $this->auth->signInWithEmailAndOobCode($email, $oobCode);

        $this->assertIsString($result->idToken());
        $this->assertNull($result->accessToken());
        $this->assertIsString($result->refreshToken());
        $this->assertIsString($result->firebaseUserId());

        $this->auth->deleteUser($user->uid);
    }

    public function testSignInAnonymously(): void
    {
        $result = $this->auth->signInAnonymously();

        $idToken = $result->idToken();

        $this->assertIsString($idToken);
        $this->assertNull($result->accessToken());
        $this->assertIsString($result->refreshToken());
        $this->assertIsString($result->firebaseUserId());

        $token = $this->auth->parseToken($idToken);

        $this->assertIsString($uid = $token->claims()->get('sub'));
        $user = $this->auth->getUser($uid);
        $this->addToAssertionCount(1);

        $this->auth->deleteUser($user->uid);
    }

    public function testSignInWithIdpAccessToken(): void
    {
        // I don't know how to retrieve a current user access token programatically, so we'll
        // test the failure case only here
        $this->expectException(FailedToSignIn::class);
        $this->auth->signInWithIdpAccessToken('google.com', 'invalid', Utils::uriFor('http://localhost'));
    }

    public function testSignInWithIdpIdToken(): void
    {
        // I don't know how to retrieve a current user access token programatically, so we'll
        // test the failure case only here
        $this->expectException(FailedToSignIn::class);
        $this->auth->signInWithIdpIdToken('google.com', 'invalid', 'http://localhost');
    }

    public function testRemoveEmailFromUser(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        try {
            $this->assertNotNull($user->email);

            $userWithoutEmail = $this->auth->updateUser($user->uid, [
                'deleteEmail' => true,
            ]);

            $this->assertNull($userWithoutEmail->email);
            $this->assertFalse($userWithoutEmail->emailVerified);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testVerifyIdTokenAcceptsResultFromParseToken(): void
    {
        $signInResult = $this->auth->signInAnonymously();
        $uid = $signInResult->firebaseUserId();
        \assert(\is_string($uid));

        try {
            $idToken = $signInResult->idToken();
            $this->assertIsString($idToken);

            $parsedToken = $this->auth->parseToken($idToken);
            $this->auth->verifyIdToken($parsedToken);

            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    protected function createUserWithEmailAndPassword(?string $email = null, ?string $password = null): UserRecord
    {
        $email ??= self::randomEmail();
        $password ??= self::randomString();

        return $this->auth->createUser([
            'email' => $email,
            'clear_text_password' => $password,
        ]);
    }
}
