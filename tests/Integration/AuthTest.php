<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\InvalidPassword;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Kreait\Firebase\Util\JSON;
use Throwable;

/**
 * @internal
 */
class AuthTest extends IntegrationTestCase
{
    /**
     * @var Auth
     */
    private $auth;

    protected function setUp()
    {
        $this->auth = self::$factory->createAuth();
    }

    public function testCreateAnonymousUser()
    {
        $user = $this->auth->createAnonymousUser();

        $this->assertNull($user->email);

        $this->auth->deleteUser($user->uid);
    }

    public function testCreateUserWithEmailAndPassword()
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $email = \uniqid('').'@domain.tld';
        $password = 'foobar';

        $check = $this->auth->createUserWithEmailAndPassword($email, $password);

        $this->assertSame($email, $check->email);
        $this->assertFalse($check->emailVerified);

        $this->auth->deleteUser($check->uid);
    }

    public function testChangeUserPassword()
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $email = \uniqid('').'@domain.tld';

        $user = $this->auth->createUserWithEmailAndPassword($email, 'old password');

        $this->auth->changeUserPassword($user->uid, 'new password');

        $this->auth->deleteUser($user->uid);
        $this->addToAssertionCount(1);
    }

    public function testChangeUserEmail()
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

    public function testSendEmailVerification()
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $uniqid = \uniqid();
        $email = "{$uniqid}@domain.tld";
        $password = 'my password';

        $user = $this->auth->createUserWithEmailAndPassword($email, $password);

        $this->auth->sendEmailVerification($user->uid, 'http://localhost', 'de');

        $this->auth->deleteUser($user->uid);
        $this->addToAssertionCount(1);
    }

    public function testSendPasswordResetEmail()
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $uniqid = \uniqid();
        $email = "{$uniqid}@domain.tld";
        $password = 'my password';

        $user = $this->auth->createUserWithEmailAndPassword($email, $password);

        $this->auth->sendPasswordResetEmail($user->email, 'http://localhost', 'de');

        $this->auth->deleteUser($user->uid);
        $this->addToAssertionCount(1);
    }

    public function testListUsers()
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

    public function testVerifyIdToken()
    {
        $user = $this->auth->createUser([]);

        $idTokenResponse = $this->auth->getApiClient()->exchangeCustomTokenForIdAndRefreshToken(
            $this->auth->createCustomToken($user->uid)
        );
        $idToken = JSON::decode((string) $idTokenResponse->getBody(), true)['idToken'];

        $this->auth->verifyIdToken($idToken);

        $this->auth->deleteUser($user->uid);
        $this->addToAssertionCount(1);
    }

    public function testRevokeRefreshTokens()
    {
        $user = $this->auth->createUser([]);

        $idTokenResponse = $this->auth->getApiClient()->exchangeCustomTokenForIdAndRefreshToken(
            $this->auth->createCustomToken($user->uid)
        );
        $idToken = JSON::decode((string) $idTokenResponse->getBody(), true)['idToken'];

        $this->auth->verifyIdToken($idToken, $checkIfRevoked = false);
        \sleep(1);

        $this->auth->revokeRefreshTokens($user->uid);

        try {
            $this->auth->verifyIdToken($idToken, $checkIfRevoked = true);
            $this->fail('An exception should have been thrown');
        } catch (RevokedIdToken $e) {
            $this->assertSame($user->uid, $e->getToken()->getClaim('user_id'));
        } catch (Throwable $e) {
            $this->fail('A '.RevokedIdToken::class.' should have been thrown');
        }

        $this->auth->deleteUser($user->uid);
    }

    public function testVerifyIdTokenString()
    {
        $user = $this->auth->createUser([]);

        $idTokenResponse = $this->auth->getApiClient()->exchangeCustomTokenForIdAndRefreshToken(
            $this->auth->createCustomToken($user->uid)
        );
        $idToken = JSON::decode((string) $idTokenResponse->getBody(), true)['idToken'];

        $this->auth->verifyIdToken((string) $idToken);

        $this->auth->deleteUser($user->uid);
        $this->addToAssertionCount(1);
    }

    public function testDisableAndEnableUser()
    {
        $user = $this->auth->createUser([]);

        $check = $this->auth->disableUser($user->uid);
        $this->assertTrue($check->disabled);

        $check = $this->auth->enableUser($user->uid);
        $this->assertFalse($check->disabled);

        $this->auth->deleteUser($user->uid);
    }

    public function testGetUser()
    {
        $user = $this->auth->createUser([]);

        $check = $this->auth->getUser($user->uid);

        $this->assertSame($user->uid, $check->uid);
        $this->assertJson(@\json_encode($check));
        $this->assertJson(@\json_encode($check->metadata));
        foreach ($check->providerData as $userInfo) {
            $this->assertJson(@\json_encode($userInfo));
        }

        $this->auth->deleteUser($user->uid);
    }

    public function testGetNonExistingUser()
    {
        $user = $this->auth->createUser([]);
        $this->auth->deleteUser($user->uid);

        $this->expectException(UserNotFound::class);
        $this->auth->getUser($user->uid);
    }

    public function testGetUserByNonExistingEmail()
    {
        $user = $this->auth->createUser([
            'email' => $email = \bin2hex(\random_bytes(5)).'@domain.tld',
        ]);
        $this->auth->deleteUser($user->uid);

        $this->expectException(UserNotFound::class);
        $this->auth->getUserByEmail($email);
    }

    public function testGetUserByPhoneNumber()
    {
        $phoneNumber = '+1234567'.\random_int(1000, 9999);

        $user = $this->auth->createUser([
            'phoneNumber' => $phoneNumber,
        ]);

        $check = $this->auth->getUserByPhoneNumber($phoneNumber);

        $this->assertSame($user->uid, $check->uid);

        $this->auth->deleteUser($user->uid);
    }

    public function testGetUserByNonExistingPhoneNumber()
    {
        $phoneNumber = '+1234567'.\random_int(1000, 9999);

        $user = $this->auth->createUser([
            'phoneNumber' => $phoneNumber,
        ]);
        $this->auth->deleteUser($user->uid);

        $this->expectException(UserNotFound::class);
        $this->auth->getUserByPhoneNumber($phoneNumber);
    }

    public function testCreateUser()
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

    public function testUpdateUserWithUidAsAdditionalArgument()
    {
        $user = $this->auth->createUser([]);
        $this->auth->updateUser($user->uid, []);
        $this->auth->deleteUser($user->uid);
        $this->addToAssertionCount(1);
    }

    public function testVerifyCorrectPassword()
    {
        $user = $this->auth->createUser(CreateUser::new()
            ->withUid($uid = \bin2hex(\random_bytes(5)))
            ->withEmail($email = $uid.'@domain.tld')
            ->withClearTextPassword($password = 'secret')
        );

        $check = $this->auth->verifyPassword($email, $password);

        $this->assertSame($user->uid, $check->uid);

        $this->auth->deleteUser($user->uid);
    }

    public function testVerifyIncorrectPassword()
    {
        $user = $this->auth->createUser(CreateUser::new()
            ->withUid($uid = \bin2hex(\random_bytes(5)))
            ->withEmail($email = $uid.'@domain.tld')
            ->withClearTextPassword('correct')
        );

        try {
            $this->auth->verifyPassword($email, 'incorrect');
            $this->fail(InvalidPassword::class.' should have been thrown');
        } catch (Throwable $e) {
            $this->assertInstanceOf(InvalidPassword::class, $e);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testDeleteNonExistingUser()
    {
        $user = $this->auth->createUser([]);

        $this->auth->deleteUser($user->uid);

        $this->expectException(UserNotFound::class);
        $this->auth->deleteUser($user->uid);
    }

    public function testSetCustomUserAttributes()
    {
        $user = $this->auth->createUser([]);

        $updatedUser = $this->auth->setCustomUserAttributes($user->uid, $claims = ['admin' => true, 'groupId' => '1234']);

        $this->assertEquals($claims, $updatedUser->customAttributes);

        $this->auth->deleteUser($user->uid);
    }

    public function testDeleteCustomUserAttributes()
    {
        $user = $this->auth->createUser([]);
        $claims = ['foo' => 'bar'];

        $updatedUser = $this->auth->setCustomUserAttributes($user->uid, $claims);
        // Make sure the claims are set, so that we can ensure that they are removed afterwards
        $this->assertEquals($claims, $updatedUser->customAttributes);

        $userWithDeletedCustomAttributes = $this->auth->deleteCustomUserAttributes($user->uid);
        $this->assertEmpty($userWithDeletedCustomAttributes->customAttributes);

        $this->auth->deleteUser($user->uid);
    }

    public function testUnlinkProvider()
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
}
