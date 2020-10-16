<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use function GuzzleHttp\Psr7\parse_query;
use function GuzzleHttp\Psr7\uri_for;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\CreateActionLink\FailedToCreateActionLink;
use Kreait\Firebase\Auth\SendActionLink\FailedToSendActionLink;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Psr\Http\Message\UriInterface;
use Throwable;

/**
 * @internal
 */
class AuthTest extends IntegrationTestCase
{
    /** @var Auth */
    private $auth;

    protected function setUp(): void
    {
        $this->auth = self::$factory->createAuth();
    }

    public function testCreateAnonymousUser(): void
    {
        $user = $this->auth->createAnonymousUser();

        $this->assertNull($user->email);

        $this->auth->deleteUser($user->uid);
    }

    public function testCreateUserWithEmailAndPassword(): void
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $email = \uniqid('').'@domain.tld';
        $password = 'foobar';

        $check = $this->auth->createUserWithEmailAndPassword($email, $password);

        $this->assertSame($email, $check->email);
        $this->assertFalse($check->emailVerified);

        $this->auth->deleteUser($check->uid);
    }

    public function testChangeUserPassword(): void
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $email = \uniqid('').'@domain.tld';

        $user = $this->auth->createUserWithEmailAndPassword($email, 'old password');

        $this->auth->changeUserPassword($user->uid, 'new password');

        $this->auth->deleteUser($user->uid);
        $this->addToAssertionCount(1);
    }

    public function testChangeUserEmail(): void
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $uniqid = \uniqid('');
        $email = "{$uniqid}@domain.tld";
        $newEmail = "{$uniqid}-changed@domain.tld";
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

        $link = $this->auth->getEmailVerificationLink((string) $user->email);

        $this->assertInstanceOf(UriInterface::class, uri_for($link));

        $this->deleteUser($user);
    }

    public function testSendEmailVerificationLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        $this->auth->sendEmailVerificationLink((string) $user->email);
        // We can't test the reception, but if we don't get an error, we consider it working
        $this->addToAssertionCount(1);

        $this->deleteUser($user);
    }

    public function testSendEmailVerificationLinkToUnknownUser(): void
    {
        $this->expectException(FailedToSendActionLink::class);
        $this->auth->sendEmailVerificationLink('unknown@domain.tld');
    }

    public function testSendEmailVerificationLinkToDisabledUser(): void
    {
        $user = $this->createUserWithEmailAndPassword();
        $this->auth->disableUser($user->uid);

        $this->expectException(FailedToSendActionLink::class);
        $this->auth->sendEmailVerificationLink((string) $user->email);

        $this->deleteUser($user);
    }

    public function testGetPasswordResetLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        $link = $this->auth->getPasswordResetLink((string) $user->email);

        $this->assertInstanceOf(UriInterface::class, uri_for($link));

        $this->deleteUser($user);
    }

    public function testSendPasswordResetLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        $this->auth->sendPasswordResetLink((string) $user->email);
        // We can't test the reception, but if we don't get an error, we consider it working
        $this->addToAssertionCount(1);

        $this->deleteUser($user);
    }

    public function testGetSignInWithEmailLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        $link = $this->auth->getSignInWithEmailLink((string) $user->email);

        $this->assertInstanceOf(UriInterface::class, uri_for($link));

        $this->deleteUser($user);
    }

    public function testSendSignInWithEmailLink(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        $this->auth->sendSignInWithEmailLink((string) $user->email);
        // We can't test the reception, but if we don't get an error, we consider it working
        $this->addToAssertionCount(1);

        $this->deleteUser($user);
    }

    public function testGetUnsupportedEmailActionLink(): void
    {
        $this->expectException(FailedToCreateActionLink::class);
        $this->auth->getEmailActionLink('unsupported', 'user@domain.tld');
    }

    public function testSendUnsupportedEmailActionLink(): void
    {
        $this->expectException(FailedToSendActionLink::class);
        $this->auth->sendEmailActionLink('unsupported', 'user@domain.tld');
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
            $this->assertInstanceOf(Auth\UserRecord::class, $userData);
            ++$count;
        }

        $this->assertSame($maxResults, $count);

        foreach ($createdUsers as $createdUser) {
            $this->auth->deleteUser($createdUser->uid);
        }
    }

    public function testVerifyIdToken(): void
    {
        $idToken = $this->auth->signInAnonymously()->idToken();
        $this->assertIsString($idToken);

        $verifiedToken = $this->auth->verifyIdToken($idToken);
        $this->addToAssertionCount(1);

        $this->auth->deleteUser($verifiedToken->getClaim('sub'));
    }

    public function testRevokeRefreshTokens(): void
    {
        $idToken = $this->auth->signInAnonymously()->idToken();
        $this->assertIsString($idToken);

        $uid = $this->auth->verifyIdToken($idToken, $checkIfRevoked = false)->getClaim('sub');

        $this->auth->revokeRefreshTokens($uid);
        \sleep(1);

        try {
            $this->auth->verifyIdToken($idToken, $checkIfRevoked = true);
        } catch (RevokedIdToken $e) {
            $this->assertSame($uid, $e->getToken()->getClaim('user_id'));
        } catch (Throwable $e) {
            throw $e;
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    public function testVerifyIdTokenString(): void
    {
        $idToken = $this->auth->signInAnonymously()->idToken();
        $this->assertIsString($idToken);

        $verifiedToken = $this->auth->verifyIdToken($idToken);

        $this->auth->deleteUser($verifiedToken->getClaim('sub'));
        $this->addToAssertionCount(1);
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
            $this->assertInstanceOf(Auth\UserRecord::class, $check[$one->uid]);
            $this->assertInstanceOf(Auth\UserRecord::class, $check[$two->uid]);
            $this->assertNull($check['non_existing']);
        } finally {
            $this->deleteUser($one);
            $this->deleteUser($two);
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
            'email' => $email = \bin2hex(\random_bytes(5)).'@domain.tld',
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
            'displayName' => $displayName = 'A display name',
            'verifiedEmail' => $email = $uid.'@domain.tld',
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

    public function testGetCustomUserAttributes(): void
    {
        $user = $this->auth->createAnonymousUser();

        try {
            $this->auth->setCustomUserClaims($user->uid, $claims = ['foo' => 'bar']);

            $this->expectDeprecation();
            $this->assertEquals($claims, $this->auth->getUser($user->uid)->customAttributes);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testSetCustomUserAttributes(): void
    {
        $user = $this->auth->createAnonymousUser();

        try {
            $this->expectDeprecation();
            $updatedUser = $this->auth->setCustomUserAttributes($user->uid, $claims = ['admin' => true, 'groupId' => '1234']);

            $this->assertEquals($claims, $updatedUser->customClaims);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testDeleteCustomUserAttributes(): void
    {
        $user = $this->auth->createAnonymousUser();

        try {
            $this->expectDeprecation();
            $this->auth->setCustomUserAttributes($user->uid, []);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
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
        $user = $this->auth->createUser([
            'uid' => $uid = \bin2hex(\random_bytes(5)),
            'verifiedEmail' => $uid.'@domain.tld',
            'phone' => '+1234567'.\random_int(1000, 9999),
        ]);

        $updatedUser = $this->auth->unlinkProvider($user->uid, 'phone');

        $this->assertNull($updatedUser->phoneNumber);

        $this->auth->deleteUser($user->uid);
    }

    public function testInvalidPasswordResetCode(): void
    {
        $this->expectException(InvalidOobCode::class);
        $this->auth->verifyPasswordResetCode('invalid');
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
        $email = \uniqid('', false).'@domain.tld';
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
        $email = \uniqid('', false).'@domain.tld';
        $password = 'my-perfect-password';

        $user = $this->createUserWithEmailAndPassword($email, $password);

        $signInLink = $this->auth->getSignInWithEmailLink($email);
        $query = (string) \parse_url($signInLink, \PHP_URL_QUERY);
        $oobCode = parse_query($query)['oobCode'] ?? '';

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
        $this->assertIsString($uid = $token->getClaim('sub', false));
        $user = $this->auth->getUser($uid);
        $this->addToAssertionCount(1);

        $this->auth->deleteUser($user->uid);
    }

    public function testSignInWithTwitterOauthCredential(): void
    {
        // We can't retrieve a credential programmatically, so we'll test the failure case only
        $this->expectException(FailedToSignIn::class);
        $this->auth->signInWithTwitterOauthCredential('access_token', 'oauth_token_secret');
    }

    public function testSignInWithGoogleIdToken(): void
    {
        // We can't retrieve a credential programmatically, so we'll test the failure case only
        $this->expectException(FailedToSignIn::class);
        $this->auth->signInWithGoogleIdToken('id_token');
    }

    public function signInWithFacebookAccessToken(): void
    {
        // We can't retrieve a credential programmatically, so we'll test the failure case only
        $this->expectException(FailedToSignIn::class);
        $this->auth->signInWithFacebookAccessToken('access_token');
    }

    public function testSignInWithIdpAccessToken(): void
    {
        // I don't know how to retrieve a current user access token programatically, so we'll
        // test the failure case only here
        $this->expectException(FailedToSignIn::class);
        $this->auth->signInWithIdpAccessToken('google.com', 'invalid', uri_for('http://localhost'));
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
            $this->deleteUser($user);
        }
    }
}
