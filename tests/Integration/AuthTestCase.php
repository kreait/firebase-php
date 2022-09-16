<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use DateInterval;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use Kreait\Firebase\Auth\CreateActionLink\FailedToCreateActionLink;
use Kreait\Firebase\Auth\CreateSessionCookie\FailedToCreateSessionCookie;
use Kreait\Firebase\Auth\SendActionLink\FailedToSendActionLink;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Exception\Auth\RevokedSessionCookie;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Tests\IntegrationTestCase;

use const PHP_URL_QUERY;

use function assert;
use function bin2hex;
use function is_array;
use function is_string;
use function parse_str;
use function parse_url;
use function random_bytes;
use function random_int;
use function sleep;

/**
 * @internal
 */
abstract class AuthTestCase extends IntegrationTestCase
{
    /**
     * @phpstan-ignore-next-line
     */
    protected Auth $auth;

    final public function testCreateAnonymousUser(): void
    {
        $user = $this->auth->createAnonymousUser();

        try {
            self::assertNull($user->email);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    final public function testCreateUserWithEmailAndPassword(): void
    {
        $email = self::randomEmail(__FUNCTION__);
        $password = 'foobar';

        try {
            $check = $this->auth->createUserWithEmailAndPassword($email, $password);

            self::assertSame($email, $check->email);
            self::assertFalse($check->emailVerified);
        } finally {
            if (!isset($check)) {
                return;
            }

            if (!$check instanceof UserRecord) {
                return;
            }
            $this->auth->deleteUser($check->uid);
        }
    }

    final public function testChangeUserPassword(): void
    {
        $email = self::randomEmail(__FUNCTION__);

        $user = $this->auth->createUserWithEmailAndPassword($email, 'old password');

        $this->auth->changeUserPassword($user->uid, 'new password');

        $this->auth->deleteUser($user->uid);
        $this->addToAssertionCount(1);
    }

    final public function testChangeUserEmail(): void
    {
        $email = self::randomEmail(__FUNCTION__ . '_1');
        $newEmail = self::randomEmail(__FUNCTION__ . '_2');
        $password = 'my password';

        $user = $this->auth->createUserWithEmailAndPassword($email, $password);

        $check = $this->auth->changeUserEmail($user->uid, $newEmail);
        self::assertSame($newEmail, $check->email);

        $refetchedUser = $this->auth->getUserByEmail($newEmail);
        self::assertSame($newEmail, $refetchedUser->email);

        $this->auth->deleteUser($user->uid);
    }

    final public function testGetEmailVerificationLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        try {
            $this->auth->getEmailVerificationLink((string) $user->email);
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    final public function testSendEmailVerificationLink(): void
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

    final public function testSendEmailVerificationLinkToUnknownUser(): void
    {
        $this->expectException(FailedToSendActionLink::class);
        $this->auth->sendEmailVerificationLink(self::randomEmail(__FUNCTION__));
    }

    final public function testSendEmailVerificationLinkToDisabledUser(): void
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

    final public function testGetPasswordResetLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        try {
            $this->auth->getPasswordResetLink((string) $user->email);
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    final public function testSendPasswordResetLink(): void
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

    final public function testGetSignInWithEmailLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        assert($user->email !== null);

        try {
            $this->auth->getSignInWithEmailLink($user->email);
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    final public function testSendSignInWithEmailLink(): void
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

    final public function testGetUnsupportedEmailActionLink(): void
    {
        $this->expectException(FailedToCreateActionLink::class);
        $this->auth->getEmailActionLink('unsupported', self::randomEmail(__FUNCTION__));
    }

    final public function testGetLocalizedEmailActionLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        self::assertIsString($user->email);

        $link = $this->auth->getEmailVerificationLink($user->email, null, 'fr');

        if (self::authIsEmulated()) {
            self::assertStringNotContainsString('lang=fr', $link);
        } else {
            self::assertStringContainsString('lang=fr', $link);
        }
    }

    final public function testSendUnsupportedEmailActionLink(): void
    {
        $this->expectException(FailedToSendActionLink::class);
        $this->auth->sendEmailActionLink('unsupported', self::randomEmail(__FUNCTION__));
    }

    final public function testListUsers(): void
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
            self::assertInstanceOf(UserRecord::class, $userData);
            ++$count;
        }

        self::assertSame($maxResults, $count);

        foreach ($createdUsers as $createdUser) {
            $this->auth->deleteUser($createdUser->uid);
        }
    }

    final public function testVerifyIdToken(): void
    {
        $result = $this->auth->signInAnonymously();

        $uid = $result->firebaseUserId();
        self::assertIsString($uid);

        try {
            $idToken = $result->idToken();
            self::assertIsString($result->firebaseUserId());
            self::assertIsString($idToken);

            $verifiedToken = $this->auth->verifyIdToken($idToken);

            self::assertSame($uid, $verifiedToken->claims()->get('sub'));

            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    final public function testRevokeRefreshTokensAfterIdTokenVerification(): void
    {
        $idToken = $this->auth->signInAnonymously()->idToken();
        self::assertIsString($idToken);

        $uid = $this->auth
            ->verifyIdToken($idToken)
            ->claims()
            ->get('sub');

        sleep(1);
        $this->auth->revokeRefreshTokens($uid);

        $this->expectException(RevokedIdToken::class);

        try {
            $this->auth->verifyIdToken($idToken, true);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    final public function testVerifyIdTokenString(): void
    {
        $result = $this->auth->signInAnonymously();

        $uid = $result->firebaseUserId();
        self::assertIsString($uid);

        $idToken = $result->idToken();
        self::assertIsString($idToken);

        try {
            $verifiedToken = $this->auth->verifyIdToken($idToken);
            self::assertSame($uid, $verifiedToken->claims()->get('sub'));
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    final public function testCreateSessionCookie(): void
    {
        $signInResult = $this->auth->signInAnonymously();

        /** @var string $uid */
        $uid = $signInResult->firebaseUserId();

        try {
            $idToken = $signInResult->idToken();
            self::assertIsString($idToken);

            $sessionCookie = $this->auth->createSessionCookie($idToken, 3600);
            self::assertIsString($sessionCookie);

            $parsed = $this->auth->parseToken($sessionCookie);

            self::assertSame($uid, $parsed->claims()->get('sub'));
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    final public function testCreateSessionCookieWithInvalidTTL(): void
    {
        $signInResult = $this->auth->signInAnonymously();

        /** @var string $uid */
        $uid = $signInResult->firebaseUserId();

        try {
            $idToken = $signInResult->idToken();
            self::assertIsString($idToken);

            $this->expectException(InvalidArgumentException::class);
            $this->auth->createSessionCookie($idToken, 5);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    final public function testCreateSessionCookieWithInvalidIdToken(): void
    {
        $this->expectException(FailedToCreateSessionCookie::class);
        $this->expectExceptionMessageMatches('/INVALID_ID_TOKEN/');

        $this->auth->createSessionCookie('invalid', 3600);
    }

    final public function testVerifySessionCookie(): void
    {
        $result = $this->auth->signInAnonymously();

        $uid = $result->firebaseUserId();
        assert(is_string($uid));

        $idToken = $result->idToken();
        assert(is_string($idToken));

        $sessionCookie = $this->auth->createSessionCookie($idToken, new DateInterval('PT5M'));

        try {
            $verifiedCookie = $this->auth->verifySessionCookie($sessionCookie);
            self::assertSame($uid, $verifiedCookie->claims()->get('sub'));
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    final public function testVerifySessionCookieAfterTokenRevocation(): void
    {
        $result = $this->auth->signInAnonymously();

        $uid = $result->firebaseUserId();
        assert(is_string($uid));

        $idToken = $result->idToken();
        assert(is_string($idToken));

        $sessionCookie = $this->auth->createSessionCookie($idToken, new DateInterval('PT5M'));

        $verifiedCookie = $this->auth->verifySessionCookie($sessionCookie, $checkIfRevoked = false);

        $uid = $verifiedCookie->claims()->get('sub');

        sleep(1);
        $this->auth->revokeRefreshTokens($uid);

        $this->expectException(RevokedSessionCookie::class);

        try {
            $this->auth->verifySessionCookie($sessionCookie, $checkIfRevoked = true);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    final public function testDisableAndEnableUser(): void
    {
        $user = $this->auth->createUser([]);

        $check = $this->auth->disableUser($user->uid);
        self::assertTrue($check->disabled);

        $check = $this->auth->enableUser($user->uid);
        self::assertFalse($check->disabled);

        $this->auth->deleteUser($user->uid);
    }

    final public function testGetUser(): void
    {
        $user = $this->auth->createUser([]);

        $check = $this->auth->getUser($user->uid);

        self::assertSame($user->uid, $check->uid);

        $this->auth->deleteUser($user->uid);
    }

    final public function testGetUsers(): void
    {
        $one = $this->auth->createAnonymousUser();
        $two = $this->auth->createAnonymousUser();

        $check = $this->auth->getUsers([$one->uid, $two->uid, 'non_existing']);

        try {
            self::assertInstanceOf(UserRecord::class, $check[$one->uid]);
            self::assertInstanceOf(UserRecord::class, $check[$two->uid]);
            self::assertNull($check['non_existing']);
        } finally {
            $this->auth->deleteUser($one->uid);
            $this->auth->deleteUser($two->uid);
        }
    }

    final public function testGetNonExistingUser(): void
    {
        $user = $this->auth->createUser([]);
        $this->auth->deleteUser($user->uid);

        $this->expectException(UserNotFound::class);
        $this->auth->getUser($user->uid);
    }

    final public function testGetUserByNonExistingEmail(): void
    {
        $user = $this->auth->createUser([
            'email' => $email = self::randomEmail(__FUNCTION__),
        ]);
        $this->auth->deleteUser($user->uid);

        $this->expectException(UserNotFound::class);
        $this->auth->getUserByEmail($email);
    }

    final public function testGetUserByPhoneNumber(): void
    {
        $phoneNumber = '+1234567' . random_int(1000, 9999);

        $user = $this->auth->createUser([
            'phoneNumber' => $phoneNumber,
        ]);

        $check = $this->auth->getUserByPhoneNumber($phoneNumber);

        self::assertSame($user->uid, $check->uid);

        $this->auth->deleteUser($user->uid);
    }

    final public function testGetUserByNonExistingPhoneNumber(): void
    {
        $phoneNumber = '+1234567' . random_int(1000, 9999);

        $user = $this->auth->createUser([
            'phoneNumber' => $phoneNumber,
        ]);
        $this->auth->deleteUser($user->uid);

        $this->expectException(UserNotFound::class);
        $this->auth->getUserByPhoneNumber($phoneNumber);
    }

    final public function testCreateUser(): void
    {
        $uid = bin2hex(random_bytes(5));
        $userRecord = $this->auth->createUser([
            'uid' => $uid,
            'displayName' => $displayName = self::randomString(__FUNCTION__),
            'verifiedEmail' => $email = self::randomEmail(__FUNCTION__),
        ]);

        self::assertSame($uid, $userRecord->uid);
        self::assertSame($displayName, $userRecord->displayName);
        self::assertTrue($userRecord->emailVerified);
        self::assertSame($email, $userRecord->email);

        $this->auth->deleteUser($uid);
    }

    final public function testUpdateUserWithUidAsAdditionalArgument(): void
    {
        $user = $this->auth->createUser([]);
        $this->auth->updateUser($user->uid, []);
        $this->auth->deleteUser($user->uid);
        $this->addToAssertionCount(1);
    }

    final public function testDeleteNonExistingUser(): void
    {
        $user = $this->auth->createUser([]);

        $this->auth->deleteUser($user->uid);

        $this->expectException(UserNotFound::class);
        $this->auth->deleteUser($user->uid);
    }

    final public function testBatchDeleteDisabledUsers(): void
    {
        $enabledOne = $this->auth->createAnonymousUser();
        $enabledTwo = $this->auth->createAnonymousUser();

        $disabled = $this->auth->createAnonymousUser();
        $this->auth->updateUser($disabled->uid, ['disabled' => true]);

        $uids = [$enabledOne->uid, $disabled->uid, $enabledTwo->uid];

        $result = $this->auth->deleteUsers($uids, false);

        self::assertSame(1, $result->successCount());
        self::assertSame(2, $result->failureCount());
        self::assertCount(2, $result->rawErrors());
    }

    final public function testBatchForceDeleteUsers(): void
    {
        $enabledOne = $this->auth->createAnonymousUser();
        $enabledTwo = $this->auth->createAnonymousUser();

        $disabled = $this->auth->createAnonymousUser();
        $this->auth->updateUser($disabled->uid, ['disabled' => true]);

        $uids = [$enabledOne->uid, $disabled->uid, $enabledTwo->uid];

        $result = $this->auth->deleteUsers($uids, true);

        self::assertSame(3, $result->successCount());
        self::assertSame(0, $result->failureCount());
        self::assertCount(0, $result->rawErrors());
    }

    final public function testSetCustomUserClaims(): void
    {
        $user = $this->auth->createAnonymousUser();

        try {
            $this->auth->setCustomUserClaims($user->uid, $claims = ['a' => 'b']);

            self::assertEquals($claims, $this->auth->getUser($user->uid)->customClaims);

            $this->auth->setCustomUserClaims($user->uid, null);

            self::assertSame([], $this->auth->getUser($user->uid)->customClaims);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    final public function testUnlinkProvider(): void
    {
        $uid = self::randomString(__FUNCTION__);

        $user = $this->auth->createUser([
            'uid' => $uid,
            'verifiedEmail' => self::randomEmail($uid),
            'phone' => '+1234567' . random_int(1000, 9999),
        ]);

        $updatedUser = $this->auth->unlinkProvider($user->uid, 'phone');

        self::assertNull($updatedUser->phoneNumber);

        $this->auth->deleteUser($user->uid);
    }

    final public function testVerifyPasswordResetCode(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        assert(is_string($user->email));

        try {
            $url = $this->auth->getPasswordResetLink($user->email);

            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

            $email = $this->auth->verifyPasswordResetCode($query['oobCode']);
            self::assertSame($email, $user->email);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    final public function testVerifyPasswordWithInvalidOobCode(): void
    {
        $this->expectException(InvalidOobCode::class);
        $this->auth->verifyPasswordResetCode('invalid');
    }

    final public function testConfirmPasswordReset(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        $url = $this->auth->getPasswordResetLink($user->email);

        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        $email = $this->auth->confirmPasswordReset($query['oobCode'], 'newPassword123');

        try {
            self::assertSame($email, $user->email);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    final public function testConfirmPasswordResetAndInvalidateRefreshTokens(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        assert(is_string($user->email));

        $url = $this->auth->getPasswordResetLink($user->email);

        $queryString = parse_url($url, PHP_URL_QUERY);
        assert(is_string($queryString));

        parse_str($queryString, $query);
        assert(is_array($query));

        $email = $this->auth->confirmPasswordReset($query['oobCode'], 'newPassword123', true);
        sleep(1); // wait for a second

        try {
            self::assertSame($email, $user->email);
            self::assertGreaterThanOrEqual($user->tokensValidAfterTime, $this->auth->getUser($user->uid)->tokensValidAfterTime);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    final public function testConfirmPasswordResetWithInvalidOobCode(): void
    {
        $this->expectException(InvalidOobCode::class);
        $this->auth->confirmPasswordReset('invalid', 'newPassword123');
    }

    final public function testSignInAsUser(): void
    {
        $user = $this->auth->createAnonymousUser();

        $result = $this->auth->signInAsUser($user);

        self::assertIsString($result->idToken());
        self::assertNull($result->accessToken());
        self::assertIsString($result->refreshToken());
        self::assertIsString($result->firebaseUserId());

        $this->auth->deleteUser($user->uid);
    }

    final public function testSignInWithCustomToken(): void
    {
        $user = $this->auth->createAnonymousUser();

        $customToken = $this->auth->createCustomToken($user->uid);

        $result = $this->auth->signInWithCustomToken($customToken);

        self::assertIsString($result->idToken());
        self::assertNull($result->accessToken());
        self::assertIsString($result->refreshToken());
        self::assertIsString($result->firebaseUserId());

        $this->auth->deleteUser($user->uid);
    }

    final public function testSignInWithRefreshToken(): void
    {
        $user = $this->auth->createAnonymousUser();

        // We need to sign in once to get a refresh token
        $firstRefreshToken = $this->auth->signInAsUser($user)->refreshToken();
        self::assertIsString($firstRefreshToken);

        $result = $this->auth->signInWithRefreshToken($firstRefreshToken);

        self::assertIsString($result->idToken());
        self::assertIsString($result->accessToken());
        self::assertIsString($result->refreshToken());
        self::assertIsString($result->firebaseUserId());

        $this->auth->deleteUser($user->uid);
    }

    final public function testSignInWithEmailAndPassword(): void
    {
        $email = self::randomEmail(__FUNCTION__);
        $password = 'my-perfect-password';

        $user = $this->createUserWithEmailAndPassword($email, $password);

        $result = $this->auth->signInWithEmailAndPassword($email, $password);

        self::assertIsString($result->idToken());
        self::assertNull($result->accessToken());
        self::assertIsString($result->refreshToken());
        self::assertIsString($result->firebaseUserId());

        $this->auth->deleteUser($user->uid);
    }

    final public function testSignInWithEmailAndOobCode(): void
    {
        $email = self::randomEmail(__FUNCTION__);
        $password = 'my-perfect-password';

        $user = $this->createUserWithEmailAndPassword($email, $password);

        $signInLink = $this->auth->getSignInWithEmailLink($email);
        $query = (string) parse_url($signInLink, PHP_URL_QUERY);
        $oobCode = Query::parse($query)['oobCode'] ?? '';

        $result = $this->auth->signInWithEmailAndOobCode($email, $oobCode);

        self::assertIsString($result->idToken());
        self::assertNull($result->accessToken());
        self::assertIsString($result->refreshToken());
        self::assertIsString($result->firebaseUserId());

        $this->auth->deleteUser($user->uid);
    }

    final public function testSignInAnonymously(): void
    {
        $result = $this->auth->signInAnonymously();

        $idToken = $result->idToken();

        self::assertIsString($idToken);
        self::assertNull($result->accessToken());
        self::assertIsString($result->refreshToken());
        self::assertIsString($result->firebaseUserId());

        $token = $this->auth->parseToken($idToken);

        self::assertIsString($uid = $token->claims()->get('sub'));
        $user = $this->auth->getUser($uid);
        $this->addToAssertionCount(1);

        $this->auth->deleteUser($user->uid);
    }

    final public function testSignInWithIdpAccessToken(): void
    {
        // I don't know how to retrieve a current user access token programatically, so we'll
        // test the failure case only here
        $this->expectException(FailedToSignIn::class);
        $this->auth->signInWithIdpAccessToken('google.com', 'invalid', Utils::uriFor('http://localhost'));
    }

    final public function testSignInWithIdpIdToken(): void
    {
        // I don't know how to retrieve a current user access token programatically, so we'll
        // test the failure case only here
        $this->expectException(FailedToSignIn::class);
        $this->auth->signInWithIdpIdToken('google.com', 'invalid', 'http://localhost');
    }

    final public function testRemoveEmailFromUser(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        try {
            self::assertNotNull($user->email);

            $userWithoutEmail = $this->auth->updateUser($user->uid, [
                'deleteEmail' => true,
            ]);

            self::assertNull($userWithoutEmail->email);
            self::assertFalse($userWithoutEmail->emailVerified);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    final public function testVerifyIdTokenAcceptsResultFromParseToken(): void
    {
        $signInResult = $this->auth->signInAnonymously();
        $uid = $signInResult->firebaseUserId();
        assert(is_string($uid));

        try {
            $idToken = $signInResult->idToken();
            self::assertIsString($idToken);

            $parsedToken = $this->auth->parseToken($idToken);
            $this->auth->verifyIdToken($parsedToken);

            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    final public function testItDownloadsOnlyAsManyAccountsAsItIsSupposedTo(): void
    {
        // Make sure we have at least two users present
        $first = $this->auth->createAnonymousUser();
        $second = $this->auth->createAnonymousUser();

        try {
            $users = $this->auth->listUsers(2, 99);
            self::assertCount(2, $users);
        } finally {
            $this->auth->deleteUser($first->uid);
            $this->auth->deleteUser($second->uid);
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
