###############
User management
###############

The Firebase Admin SDK for PHP provides an API for managing your Firebase users with elevated privileges.
The admin user management API gives you the ability to programmatically retrieve, create, update, and
delete users without requiring a user's existing credentials and without worrying about client-side
rate limiting.

.. code-block:: php

    use Kreait\Firebase\Factory;
    use Kreait\Firebase\ServiceAccount;

    $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/google-service-account.json');

    $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->create();

    $auth = $firebase->getAuth();

************
User Records
************

``UserRecord`` s returned by methods from the ``Kreait\Firebase\Auth`` class have the
following signature:

.. code-block:: json

    {
        "uid": "jEazVdPDhqec0tnEOG7vM5wbDyU2",
        "email": "user@domain.tld",
        "emailVerified": true,
        "displayName": null,
        "photoUrl": null,
        "phoneNumber": null,
        "disabled": false,
        "metadata": {
            "createdAt": "2018-02-14T15:41:32+00:00",
            "lastLoginAt": "2018-02-14T15:41:32+00:00"
        },
        "providerData": [
            {
                "uid": "user@domain.tld",
                "displayName": null,
                "email": "user@domain.tld",
                "photoUrl": null,
                "providerId": "password",
                "phoneNumber": null
            }
        ],
        "passwordHash": "UkVEQUNURUQ=",
        "customClaims": null,
        "tokensValidAfterTime": "2018-02-14T15:41:32+00:00"
    }

**********
List users
**********

To enhance performance and prevent memory issues when retrieving a huge amount of users,
this methods returns a `Generator <http://php.net/manual/en/language.generators.overview.php>`_.

.. code-block:: php

    $users = $auth->listUsers($defaultMaxResults = 1000, $defaultBatchSize = 1000);

    foreach ($users as $user) {
        /** @var \Kreait\Firebase\Auth\UserRecord $user */
        // ...
    }
    // or
    array_map(function (\Kreait\Firebase\Auth\UserRecord $user) {
        // ...
    }, iterator_to_array($users));


*************************************
Get information about a specific user
*************************************

.. code-block:: php

    $user = $auth->getUser('some-uid');
    $user = $auth->getUserByEmail('user@domain.tld');

*************
Create a user
*************

The Admin SDK provides a method that allows you to create a new Firebase Authentication user.
This method accepts an object containing the profile information to include in the newly created user account:

.. code-block:: php

    $userProperties = [
        'email' => 'user@example.com',
        'emailVerified' => false,
        'phoneNumber' => '+15555550100',
        'password' => 'secretPassword',
        'displayName' => 'John Doe',
        'photoUrl' => 'http://www.example.com/12345678/photo.png',
        'disabled' => false,
    ];

    $createdUser = $auth->createUser($userProperties);

    // This is equivalent to:

    $request = \Kreait\Auth\Request\CreateUser::new()
        ->withUnverifiedEmail('user@example.com')
        ->withPhoneNumber('+15555550100')
        ->withClearTextPassword('secretPassword')
        ->withDisplayName('John Doe')
        ->withPhotoUrl('http://www.example.com/12345678/photo.png');

    $createdUser = $auth->createUser($request);

By default, Firebase Authentication will generate a random uid for the new user.
If you instead want to specify your own uid for the new user, you can include
in the properties passed to the user creation method:

.. code-block:: php

    $properties = [
        'uid' => 'some-uid',
        // other properties
    ];

    $request = \Kreait\Auth\Request\CreateUser::new()
        ->withUid('some-uid')
        // with other properties
    ;

Any combination of the following properties can be provided:

================= ======= ===========
Property          Type    Description
================= ======= ===========
``uid``	          string  The uid to assign to the newly created user. Must be a string between 1 and 128 characters long, inclusive. If not provided, a random uid will be automatically generated.
``email``         string  The user's primary email. Must be a valid email address.
``emailVerified`` boolean Whether or not the user's primary email is verified. If not provided, the default is false.
``phoneNumber``	  string  The user's primary phone number. Must be a valid E.164 spec compliant phone number.
``password``      string  The user's raw, unhashed password. Must be at least six characters long.
``displayName``   string  The users' display name.
``photoURL``      string  The user's photo URL.
``disabled``      boolean Whether or not the user is disabled. true for disabled; false for enabled. If not provided, the default is false.
================= ======= ===========

.. note::
    All of the above properties are optional. If a certain property is not specified,
    the value for that property will be empty unless a default is mentioned
    in the above table.

.. note::
    If you provide none of the properties, an anonymous user will be created.

*************
Update a user
*************

Updating a user works exactly as creating a new user, except that the ``uid`` property is required:

.. code-block:: php

    $uid = 'some-uid';
    $properties = [
        'displayName' => 'New display name'
    ];

    $updatedUser = $auth->updateUser($uid, $properties);

    $request = \Kreait\Auth\Request\UpdateUser::new()
        ->withDisplayName('New display name');

    $updatedUser = $auth->updateUser($uid, $request);

In addition to the properties of a create request, the following properties can be provided:

====================== ============ ===========
Property               Type         Description
====================== ============ ===========
``deletePhotoUrl``     boolean      Whether or not to delete the user's photo.
``deleteDisplayName``  boolean      Whether or not to delete the user's display name.
``deletePhoneNumber``  boolean      Whether or not to delete the user's phone number.
``deleteProvider``     string|array One or more identity providers to delete.
``customAttributes``   array        A list of custom attributes which will be available in a User's ID token.
====================== ============ ===========

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

************************
Update custom attributes
************************

.. code-block:: php

    $uid = 'some-uid';
    $customAttributes = [
        'admin' => true,
        'groupId' => '1234'
    ];

    $updatedUser = $auth->setCustomUserAttributes($uid, $customAttributes);
    $userWithDeletedCustomAttributes = $auth->deleteCustomUserAttributes($uid);

.. note::
    Learn more about custom attributes/claims in the official documentation:
    `Control Access with Custom Claims and Security Rules <https://firebase.google.com/docs/auth/admin/custom-claims>`_

*************
Delete a user
*************

.. code-block:: php

    $uid = 'some-uid';

    $auth->deleteUser($uid);

*****************
Verify a password
*****************

.. warning::
    This method has the side effect of changing the last login timestamp of the given user. The recommended way
    to authenticate users in a client/server environment is to use a Firebase Client SDK to authenticate
    the user and to send an ID Token generated by the client back to the server.

.. code-block:: php

    try {
        $user = $auth->verifyPassword($email, $password);
    } catch (Kreait\Firebase\Exception\Auth\InvalidPassword $e) {
        echo $e->getMessage();
    }


***********************
Verify an email address
***********************

Unless a user verifies the email address assigned to them, their email address will be markes as ``unverified``.

You can send a verification email to a user with the following method:

.. code-block:: php

    $auth->sendEmailVerification($uid);

    // The method has an optional second parameter to specify where the user should be redirected
    // to after they have have verified their email address
    $auth->sendEmailVerification($uid, 'https//my-application.com/email-verified');

    // The method has an optional third parameter to specifiy the locale of the sent email
    // Without it, your Firebase project's configured default language will be used
    $auth->sendEmailVerification('user@domain.tld', null, 'de');


***************************
Send a password reset email
***************************

You can send an email allowing a user to reset their password with the following method:

.. code-block:: php

    $auth->sendPasswordResetEmail('user@domain.tld');

    // The method has an optional second parameter to specify where the user should be redirected
    // to after they have have reset their password
    $auth->sendPasswordResetEmail('user@domain.tld', 'https//my-application.com/password-reset');

    // The method has an optional third parameter to specifiy the locale of the sent email
    // Without it, your Firebase project's configured default language will be used
    $auth->sendPasswordResetEmail('user@domain.tld', null, 'fr');


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

.. note::
    Because Firebase ID tokens are stateless JWTs, you can determine a token has been revoked only by requesting the
    token's status from the Firebase Authentication backend. For this reason, performing this check on your server
    is an expensive operation, requiring an extra network round trip. You can avoid making this network request
    by setting up Firebase Rules that check for revocation rather than using the Admin SDK to make the check.

    For more information, please visit
    `Google: Detect ID token revocation in Database Rules <https://firebase.google.com/docs/auth/admin/manage-sessions#detect_id_token_revocation_in_database_rules>`_


.. rubric:: References

.. [#f1] `Google: Revoke refresh tokens <https://firebase.google.com/docs/reference/admin/node/admin.auth.Auth#revokeRefreshTokens>`_
