##############
Authentication
##############

Before you start, please read about Firebase Authentication in the official documentation:

* `Introduction to the Admin Database API <https://firebase.google.com/docs/database/admin/start>`_
* `Create custom tokens <https://firebase.google.com/docs/auth/admin/create-custom-tokens>`_
* `Verify ID Tokens <https://firebase.google.com/docs/auth/admin/verify-id-tokens>`_
* `Manage Session Cookies <https://firebase.google.com/docs/auth/admin/manage-cookies>`_
* `Revoke refresh tokens <https://firebase.google.com/docs/reference/admin/node/admin.auth.Auth#revokeRefreshTokens>`_

Before you can access the Firebase Realtime Database from a server using the Firebase Admin SDK,
you must authenticate your server with Firebase. When you authenticate a server, rather than
sign in with a user account's credentials as you would in a client app, you authenticate
with a `service account <https://developers.google.com/identity/protocols/OAuth2ServiceAccount>`_
which identifies your server to Firebase.

You can get two different levels of access when you authenticate using the Firebase Admin SDK:

**Administrative privileges**: Complete read and write access to a project's Realtime Database.
Use with caution to complete administrative tasks such as data migration or restructuring
that require unrestricted access to your project's resources.

**Limited privileges**: Access to a project's Realtime Database, limited to only the resources
your server needs. Use this level to complete administrative tasks that have well-defined
access requirements. For example, when running a summarization job that reads data
across the entire database, you can protect against accidental writes by setting
a read-only security rule and then initializing the Admin SDK with privileges
limited by that rule.

*******************************
Initializing the Auth component
*******************************

**With the SDK**

.. code-block:: php

    $auth = $factory->createAuth();

**With Dependency Injection** (`Symfony Bundle <https://github.com/kreait/firebase-bundle>`_/`Laravel/Lumen Package <https://github.com/kreait/laravel-firebase>`_)

.. code-block:: php

    use Kreait\Firebase\Contract\Auth;

    class MyService
    {
        public function __construct(Auth $auth)
        {
            $this->auth = $auth;
        }
    }

**With the Laravel** ``app()`` **helper** (`Laravel/Lumen Package <https://github.com/kreait/laravel-firebase>`_)

.. code-block:: php

    $auth = app('firebase.auth');


.. _create-custom-tokens:

********************
Create custom tokens
********************

The Firebase Admin SDK has a built-in method for creating custom tokens. At a minimum, you need to provide a uid,
which can be any string but should uniquely identify the user or device you are authenticating.
These tokens expire after one hour.

.. code-block:: php

    $uid = 'some-uid';

    $customToken = $auth->createCustomToken($uid);

You can also optionally specify additional claims to be included in the custom token. For example,
below, a premiumAccount field has been added to the custom token, which will be available in
the auth / request.auth objects in your Security Rules:

.. code-block:: php

    $uid = 'some-uid';
    $additionalClaims = [
        'premiumAccount' => true
    ];

    $customToken = $auth->createCustomToken($uid, $additionalClaims);

    $customTokenString = $customToken->toString();

.. note::
    This library uses `lcobucci/jwt <https://github.com/lcobucci/jwt>`_ to work with JSON Web Tokens (JWT).
    You can find the usage instructions at `https://lcobucci-jwt.readthedocs.io/ <https://lcobucci-jwt.readthedocs.io/>`_.

.. _verify-a-firebase-id-token:

**************************
Verify a Firebase ID Token
**************************

If a Firebase client app communicates with your server, you might need to identify the currently signed-in user.
To do so, verify the integrity and authenticity of the ID token and retrieve the uid from it.
You can use the uid transmitted in this way to securely identify the currently signed-in user on your server.

.. note::
    Many use cases for verifying ID tokens on the server can be accomplished by using Security Rules for the
    `Firebase Realtime Database <https://firebase.google.com/docs/database/security/>`_ and
    `Cloud Storage <https://firebase.google.com/docs/storage/security/>`_.
    See if those solve your problem before verifying ID tokens yourself.

.. warning::
    The ID token verification methods included in the Firebase Admin SDKs are meant to verify ID tokens that come
    from the client SDKs, not the custom tokens that you create with the Admin SDKs.
    See `Auth tokens <https://firebase.google.com/docs/auth/users/#auth_tokens>`_
    for more information.

Use ``Auth::verifyIdToken()`` to verify an ID token:

.. code-block:: php

    use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

    $idTokenString = '...';

    try {
        $verifiedIdToken = $auth->verifyIdToken($idTokenString);
    } catch (FailedToVerifyToken $e) {
        echo 'The token is invalid: '.$e->getMessage();
    }

    $uid = $verifiedIdToken->claims()->get('sub');

    $user = $auth->getUser($uid);

``Auth::verifyIdToken()`` accepts the following parameters:

============================ ================= ===========
Parameter                    Type              Description
============================ ================= ===========
``idToken``                  string|Token      **(required)** The ID token to verify
``checkIfRevoked``           boolean           (optional, default: ``false`` ) check if the ID token is revoked
``leewayInSeconds``          positive-int|null (optional, default: ``null``) number of seconds to allow a token to be expired, in case that there is a clock skew between the signing and the verifying server.
============================ ================= ===========

.. note::
    This library uses `lcobucci/jwt <https://github.com/lcobucci/jwt>`_ to work with JSON Web Tokens (JWT).
    You can find the usage instructions at `https://lcobucci-jwt.readthedocs.io/ <https://lcobucci-jwt.readthedocs.io/>`_.


***************************
Custom Authentication Flows
***************************

.. warning::
    It is recommended that you use the Firebase Client SDKs to perform user authentication. Once
    signed in via a client SDK, you should pass the logged-in user's current ID token to your
    PHP endpoint and :ref:`verify the ID token <verify-a-firebase-id-token>` with each request
    to your backend.

Each of the methods documented below will return an instance of ``Kreait\Firebase\Auth\SignInResult\SignInResult``
with the following accessors:

.. code-block:: php

    $signInResult->idToken(); // string|null
    $signInResult->firebaseUserId(); // string|null
    $signInResult->accessToken(); // string|null
    $signInResult->refreshToken(); // string|null
    $signInResult->data(); // array
    $signInResult->asTokenResponse(); // array

``SignInResult::data()`` returns the full payload of the response returned by the Firebase API,
``SignInResult::asTokenResponse()`` returns the Sign-In result in a format that can be returned to
clients:

.. code-block:: php

    $tokenResponse = [
        'token_type' => 'Bearer',
        'access_token' => '...',
        'id_token' => '...',
        'refresh_token' => '...',
        'expires_in' => 3600,
    ];

.. note::
    Not all sign-in methods return all types of tokens.


Anonymous Sign In
-----------------

.. note::
    This method will create a new user in the Firebase Auth User Database each time
    it is invoked

.. code-block:: php

    $signInResult = $auth->signInAnonymously();


Sign In with Email and Password
-------------------------------

.. code-block:: php

    $signInResult = $auth->signInWithEmailAndPassword($email, $clearTextPassword);


Sign In with Email and Oob Code
-------------------------------

.. code-block:: php

    $signInResult = $auth->signInWithEmailAndOobCode($email, $oobCode);


Sign In with a Custom Token
---------------------------

.. code-block:: php

    $signInResult = $auth->signInWithCustomToken($customToken);


Sign In with a Refresh Token
----------------------------

.. code-block:: php

    $signInResult = $auth->signInWithRefreshToken($refreshToken);


Sign In with IdP credentials
----------------------------

IdP (Identity Provider) credentials are credentials provided by authentication providers other than Firebase,
for example Facebook, Github, Google or Twitter. You can find the currently supported authentication providers
in the
`official Firebase documentation <https://firebase.google.com/docs/projects/provisioning/configure-oauth#add-idp>`_.

This could be useful if you already have "Sign in with X" implemented in your application, and want to
authenticate the same user with Firebase.

Once you have received those credentials, you can use them to sign a user in with them:

.. code-block:: php

    $signInResult = $auth->signInWithIdpAccessToken($provider, string $accessToken, $redirectUrl = null, ?string $oauthTokenSecret = null, ?string $linkingIdToken = null, ?string $rawNonce = null);

    $signInResult = $auth->signInWithIdpIdToken($provider, $idToken, $redirectUrl = null, ?string $linkingIdToken = null, ?string $rawNonce = null);


Sign In without a token
-----------------------

.. code-block:: php

    $signInResult = $auth->signInAsUser($userOrUid, array $claims = null);


Linking and Unlinking Identity Providers
----------------------------------------

For linking IdP you can add use any of above methods for signing in with IdP credentials, by providing the ID token of
a user to link to as an additional parameter:

.. code-block:: php

    $signInResult = $auth->signInWithIdpAccessToken($provider, $accessToken, $redirectUrl = null, $oauthTokenSecret = null, $linkingIdToken);
    $signInResult = $auth->signInWithGoogleIdToken($idToken, $redirectUrl = null, $linkingIdToken);

You can unlink a provider from a given user with the ``unlinkProvider()`` method:

.. code-block:: php

    $auth->unlinkProvider($uid, $provider)


************************
Invalidate user sessions
************************

This will revoke all sessions for a specified user and disable any new ID tokens for existing sessions from getting
minted. **Existing ID tokens may remain active until their natural expiration (one hour).** To verify that
ID tokens are revoked, use ``Auth::verifyIdToken()`` with the second parameter set to ``true``.

If the check fails, a ``RevokedIdToken`` exception will be thrown.

.. code-block:: php

    use Kreait\Firebase\Exception\Auth\RevokedIdToken;

    $auth->revokeRefreshTokens($uid);

    try {
        $verifiedIdToken = $auth->verifyIdToken($idTokenString, $checkIfRevoked = true);
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

***************
Session Cookies
***************

Before you start, please read about Firebase Session Cookies in the official documentation:

* `Manage Session Cookies <https://firebase.google.com/docs/auth/admin/manage-cookies>`_

Create session cookie
---------------------

.. warning::
    Creating session cookies when using tenants is currently not possible. Please follow
    `this issue on GitHub <https://github.com/firebase/firebase-admin-python/issues/577>`_ or
    `in the Google Issue Tracker <https://issuetracker.google.com/issues/204377229>`_ for updates.

Given an ID token sent to your server application from a client application, you can convert it to a session cookie:

.. code-block:: php

    use Kreait\Firebase\Auth\CreateSessionCookie\FailedToCreateSessionCookie;

    $idToken = '...';

    // The TTL must be between 5 minutes and 2 weeks and can be provided as
    // an integer value in seconds or a DateInterval

    $fiveMinutes = 300;
    $oneWeek = new \DateInterval('P7D');

    try {
        $sessionCookieString = $auth->createSessionCookie($idToken, $oneWeek);
    } catch (FailedToCreateSessionCookie $e) {
        echo $e->getMessage();
    }

****************
Tenant Awareness
****************

.. note::
    Multi-tenancy support requires Google Cloud's Identity Platform (GCIP). To learn more about GCIP,
    including pricing and features, see the `GCIP documentation <https://cloud.google.com/identity-platform?hl=zh-Cn>`_.

    Before multi-tenancy can be used on a Google Cloud Identity Platform project, tenants must be allowed on that
    project via the Cloud Console UI.

In order to manage users, create custom tokens, verify ID tokens and sign in users in the scope of a tenant, you
can configure the factory with a tenant ID:

.. code-block:: php

    $tenantUnawareAuth = $factory->createAuth();

    $tenantAwareAuth = $factory
        ->withTenantId('my-tenant-id')
        ->createAuth();
