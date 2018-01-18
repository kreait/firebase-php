###############
User management
###############

You can enable user management features by providing your project's web API key
to the Firebase factory and getting an ``Auth`` instance:

.. code-block:: php

    use Kreait\Firebase\Factory;
    use Kreait\Firebase\ServiceAccount;

    $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/google-service-account.json');

    $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->create();

    $auth = $firebase->getAuth();

**********
List users
**********

To enhance performance and prevent memory issues when retrieving a huge amount of users,
this methods returns a `Generator <http://php.net/manual/en/language.generators.overview.php>`_.

.. code-block:: php

    $users = $auth->listUsers($defaultMaxResults = 1000, $defaultBatchSize = 1000);

    foreach ($users as $user) {
        print_r($user);
    }
    // or
    array_map(function (array $userData) {
        print_r($userData);
    }, iterator_to_array($users));


*************************************
Get information about a specific user
*************************************

.. code-block:: php

    $userInfo = $auth->getUserInfo('some-uid');


************************
Create an anonymous user
************************

.. code-block:: php

    $auth->createAnonymousUser();

*************************************
Create a user with email and password
*************************************

.. code-block:: php

    $auth->createUserWithEmailAndPassword('user@domain.tld', 'a secure password');

************************
Change a user's password
************************

.. code-block:: php

    $uid = 'some-uid';

    $updatedUser = $auth->changeUserPassword($uid, 'new password');

*********************
Change a user's email
*********************

.. code-block:: php

    $uid = 'some-uid';

    $updatedUser = $auth->changeUserEmail($uid, 'user@domain.tld');

**************
Disable a user
**************

.. code-block:: php

    $uid = 'some-uid';

    $updatedUser = $auth->disableUser($uid);


*************
Enable a user
*************

.. code-block:: php

    $uid = 'some-uid';

    $updatedUser = $auth->enableUser($uid);


*************
Delete a user
*************

.. code-block:: php

    $uid = 'some-uid';

    $auth->deleteUser($uid);

***************************
Send a password reset email
***************************

.. code-block:: php

    $email = 'user@domain.tld';

    $auth->sendPasswordResetEmail($email);

*******************************
Invalidate user sessions [#f1]_
*******************************

This will revoke all sessions for a specified user and disable any new ID tokens for existing sessions from getting
minted. **Existing ID tokens may remain active until their natural expiration (one hour).** To verify that
ID tokens are revoked, use ``Auth::verifyIdToken()`` with the second parameter set to ``true``.

If the check fails, a ``RevokedIdToken`` exception will be thrown.

.. code-block:: php

    use Kreait\Firebase\Exception\Auth\RevokedIdToken;

    $idTokenString = '...';

    $verifiedIdToken = $firebase->getAuth()->verifyIdToken($idTokenString);

    $uid = $verifiedIdToken->getClaim('sub');

    $firebase->getAuth()->revokeRefreshTokens($uid);

    try {
        $verifiedIdToken = $firebase->getAuth()->verifyIdToken($idTokenString, true);
    } catch (RevokedIdToken $e) {
        echo $e->getMessage();
    }


.. rubric:: References

.. [#f1] `Google: Revoke refresh tokens <https://firebase.google.com/docs/reference/admin/node/admin.auth.Auth#revokeRefreshTokens>`_
