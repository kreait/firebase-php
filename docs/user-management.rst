###############
User management
###############

The Firebase Admin SDK for PHP provides an API for managing your Firebase users with elevated privileges.
The admin user management API gives you the ability to programmatically retrieve, create, update, and
delete users without requiring a user's existing credentials and without worrying about client-side
rate limiting.

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
            "lastLoginAt": "2018-02-14T15:41:32+00:00",
            "passwordUpdatedAt": "2018-02-14T15:42:19+00:00",
            "lastRefreshAt": "2018-02-14T15:42:19+00:00"
        },
        "providerData": [
            {
                "uid": "user@domain.tld",
                "displayName": null,
                "screenName": null,
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

    try {
        $user = $auth->getUser('some-uid');
        $user = $auth->getUserByEmail('user@domain.tld');
        $user = $auth->getUserByPhoneNumber('+49-123-456789');
    } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
        echo $e->getMessage();
    }

************************************
Get information about multiple users
************************************

You can retrieve multiple user records by using ``$auth->getUsers()``. When a user doesn't exist,
no exception is thrown, but its entry in the result set is null:

.. code-block:: php

    $users = $auth->getUsers(['some-uid', 'another-uid', 'non-existing-uid']);

Result:

.. code-block:: text

    [
       'some-uid' => <UserRecord>,
       'another-uid' => <UserRecord>,
       'non-existing-uid' => null
    ]




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
``deleteEmail``        boolean      Whether or not to delete the user's email.
``deletePhotoUrl``     boolean      Whether or not to delete the user's photo.
``deleteDisplayName``  boolean      Whether or not to delete the user's display name.
``deletePhoneNumber``  boolean      Whether or not to delete the user's phone number.
``deleteProvider``     string|array One or more identity providers to delete.
``customAttributes``   array        A list of custom attributes which will be available in a User's ID token.
====================== ============ ===========

.. note::

    When deleting the email from an existing user, the password authentication provider
    will be disabled (the user can't log in with an email and password combination
    anymore). After adding a new email to the same user, the previously set password
    might be restored. If you just want to change a user's email, consider updating
    the email field directly.

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

******************
Custom user claims
******************

.. note::

    Learn more about custom attributes/claims in the official documentation:
    `Control Access with Custom Claims and Security Rules <https://firebase.google.com/docs/auth/admin/custom-claims>`_

.. code-block:: php

    // The new custom claims will propagate to the user's ID token the
    // next time a new one is issued.
    $auth->setCustomUserClaims($uid, ['admin' => true, 'key1' => 'value1']);

    // Retrieve a user's current custom claims
    $claims = $auth->getUser($uid)->customClaims;

    // Remove a user's custom claims
    $auth->setCustomUserClaims($uid, null);

The custom claims object should not contain any `OIDC <http://openid.net/specs/openid-connect-core-1_0.html#IDToken>`_
reserved key names or Firebase reserved names. Custom claims payload must not exceed 1000 bytes.

*************
Delete a user
*************

The Firebase Admin SDK allows deleting users by their ``uid``:

.. code-block:: php

    $uid = 'some-uid';

    try {
        $auth->deleteUser($uid);
    catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
        echo $e->getMessage();
    } catch (\Kreait\Firebase\Exception\AuthException $e) {
        echo 'Deleting
    }

This method returns nothing when the deletion completes successfully. If the provided ``uid`` does not correspond
to an existing user or the user cannot be deleted for any other reason, the delete user method throws an error.

*********************
Delete multiple users
*********************

The Firebase Admin SDK can also delete multiple (up to 1000) users at once:

.. code-block:: php

    $uid = ['uid-1', 'uid-2', 'uid-3'];
    $forceDeleteEnabledUsers = true; // default: false

    $result = $auth->deleteUsers($uids, $forceDeleteEnabledUsers);

By default, only disabled users will be deleted. If you want to also delete enabled users,
use ``true`` as the second argument.

This method always returns an instance of ``Kreait\Firebase\Auth\DeleteUsersResult``:

.. code-block:: php

    $successCount = $result->successCount();
    $failureCount = $result->failureCount();
    $errors = $result->rawErrors();

.. note::
    Using this method to delete multiple users at once will not trigger ``onDelete()`` event handlers for
    Cloud Functions for Firebase. This is because batch deletes do not trigger a user deletion event on each user.
    Delete users one at a time if you want user deletion events to fire for each deleted user.


************************
Using Email Action Codes
************************

The Firebase Admin SDK provides the ability to send users emails containing links they can use for password resets,
email address verification, and email-based sign-in. These emails are sent by Google and have limited
customizability.

If you want to instead use your own email templates and your own email delivery service, you can use the
Firebase Admin SDK to programmatically generate the action links for the above flows, which you can
include in emails to your users.

Action Code Settings
====================

.. note::
    Action Code Settings are optional.

Action Code Settings allow you to pass additional state via a continue URL which is accessible after the user clicks
the email link. This also provides the user the ability to go back to the app after the action is completed.
In addition, you can specify whether to handle the email action link directly from a mobile application
when it is installed or from a browser.

For links that are meant to be opened via a mobile app, you’ll need to enable Firebase Dynamic Links and perform some
tasks to detect these links from your mobile app. Refer to the instructions on how to
`configure Firebase Dynamic Links <https://firebase.google.com/docs/auth/web/passing-state-in-email-actions#configuring_firebase_dynamic_links>`_
for email actions.

========================= =========== ===========
Parameter                 Type        Description
========================= =========== ===========
``continueUrl``	          string|null Sets the continue URL
``url``	                  string|null Alias for ``continueUrl``
``handleCodeInApp``       bool|null    | Whether the email action link will be opened in a mobile app or a web link first.
                                       | The default is false. When set to true, the action code link will be be sent
                                       | as a Universal Link or Android App Link and will be opened by the app if
                                       | installed. In the false case, the code will be sent to the web widget first
                                       | and then on continue will redirect to the app if installed.
``androidPackageName``    string|null  | Sets the Android package name. This will try to open the link in an android app
                                       | if it is installed.
``androidInstallApp``     bool|null    | Whether to install the Android app if the device supports it and the app is not
                                       | already installed. If this field is provided without a ``androidPackageName``,
                                       | an error is thrown explaining that the packageName must be provided in
                                       | conjunction with this field.
``androidMinimumVersion`` string|null  | If specified, and an older version of the app is installed,
                                       | the user is taken to the Play Store to upgrade the app.
                                       | The Android app needs to be registered in the Console.
``iOSBundleId``           string|null  | Sets the iOS bundle ID. This will try to open the link in an iOS app if it is
                                       | installed. The iOS app needs to be registered in the Console.
========================= =========== ===========

Example:

.. code-block:: php

    $actionCodeSettings = [
        'continueUrl' => 'https://www.example.com/checkout?cartId=1234',
        'handleCodeInApp' => true,
        'dynamicLinkDomain' => 'coolapp.page.link',
        'androidPackageName' => 'com.example.android',
        'androidMinimumVersion' => '12',
        'androidInstallApp' => true,
        'iOSBundleId' => 'com.example.ios',
    ];


Email verification
==================

To generate an email verification link, provide the existing user’s unverified email and optional Action Code Settings.
The email used must belong to an existing user. Depending on the method you use, an email will be sent to the user,
or you will get an email action link that you can use in a custom email.

.. code-block:: php

    $link = $auth->getEmailVerificationLink($email);
    $link = $auth->getEmailVerificationLink($email, $actionCodeSettings);

    $auth->sendEmailVerificationLink($email);
    $auth->sendEmailVerificationLink($email, $actionCodeSettings);
    $auth->sendEmailVerificationLink($email, null, $locale);
    $auth->sendEmailVerificationLink($email, $actionCodeSettings, $locale);

Password reset
==============

To generate a password reset link, provide the existing user’s email and optional Action Code Settings.
The email used must belong to an existing user. Depending on the method you use, an email will be sent to the user,
or you will get an email action link that you can use in a custom email.

.. code-block:: php

    $link = $auth->getPasswordResetLink($email);
    $link = $auth->getPasswordResetLink($email, $actionCodeSettings);

    $auth->sendPasswordResetLink($email);
    $auth->sendPasswordResetLink($email, $actionCodeSettings);
    $auth->sendPasswordResetLink($email, null, $locale);
    $auth->sendPasswordResetLink($email, $actionCodeSettings, $locale);

Email link for sign-in
======================

.. note::

    Before you can authenticate users with email link sign-in, you will need to enable
    `email link sign-in <https://firebase.google.com/docs/auth/web/email-link-auth#enable_email_link_sign-in_for_your_firebase_project>`_
    for your Firebase project.

.. note::

    Unlike password reset and email verification, the email used does not necessarily need to belong to an existing user,
    as this operation can be used to sign up new users into your app via email link.

.. note::

    The ActionCodeSettings object is required in this case to provide information on where to return the user after the
    link is clicked for sign-in completion.

To generate a sign-in link, provide the user’s email and Action Code Settings. Depending on the method you use,
an email will be sent to the user, or you will get an email action link that you can use in a custom email.

.. code-block:: php

    $link = $auth->getSignInWithEmailLink($email, $actionCodeSettings);

    $auth->sendSignInWithEmailLink($email, $actionCodeSettings);
    $auth->sendSignInWithEmailLink($email, $actionCodeSettings, $locale);

Confirm a password reset
========================

.. note::
    Out of the box, Firebase handles the confirmation of password reset requests. You can use your own
    server to handle account management emails by following the instructions on
    `Customize account management emails and SMS messages <https://support.google.com/firebase/answer/7000714>`_

.. code-block:: php

    $oobCode = '...'; // Extract the OOB code from the request url (not scope of the SDK (yet :)))
    $newPassword = '...';
    $invalidatePreviousSessions = true; // default, will revoke current user refresh tokens

    try {
        $auth->confirmPasswordReset($oobCode, $newPassword, $invalidatePreviousSessions);
    } catch (\Kreait\Firebase\Exception\Auth\ExpiredOobCode $e) {
        // Handle the case of an expired reset code
    } catch (\Kreait\Firebase\Exception\Auth\InvalidOobCode $e) {
        // Handle the case of an invalid reset code
    } catch (\Kreait\Firebase\Exception\AuthException $e) {
        // Another error has occurred
    }

