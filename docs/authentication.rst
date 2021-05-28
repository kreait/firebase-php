##############
Authentication
##############

Before you start, please read about Firebase Authentication in the official documentation:

* `Introduction to the Admin Database API <https://firebase.google.com/docs/database/admin/start>`_
* `Create custom tokens <https://firebase.google.com/docs/auth/admin/create-custom-tokens>`_
* `Verify ID Tokens <https://firebase.google.com/docs/auth/admin/verify-id-tokens>`_
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

    use Kreait\Firebase\Auth;

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

    use Firebase\Auth\Token\Exception\InvalidToken;

    $idTokenString = '...';

    try {
        $verifiedIdToken = $auth->verifyIdToken($idTokenString);
    } catch (InvalidToken $e) {
        echo 'The token is invalid: '.$e->getMessage();
    } catch (\InvalidArgumentException $e) {
        echo 'The token could not be parsed: '.$e->getMessage();
    }

    // if you're using lcobucci/jwt ^4.0
    $uid = $verifiedIdToken->claims()->get('sub');
    // or, if you're using lcobucci/jwt ^3.0
    $uid = $verifiedIdToken->claims()->get('sub');

    $user = $auth->getUser($uid);

``Auth::verifyIdToken()`` accepts the following parameters:

============================ ============ ===========
Parameter                    Type         Description
============================ ============ ===========
``idToken``                  string|Token **(required)** The ID token to verify
``checkIfRevoked``           boolean      (optional, default: ``false`` ) check if the ID token is revoked
============================ ============ ===========

.. note::
    A leeway of 5 minutes is applied when verifying time based claims starting with release 4.25.0

.. note::
    This library uses `lcobucci/jwt <https://github.com/lcobucci/jwt>`_ to work with JSON Web Tokens (JWT).
    You can find the usage instructions at
    `https://github.com/lcobucci/jwt/blob/3.2/README.md <https://github.com/lcobucci/jwt/blob/3.2/README.md>`_.


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

    use Kreait\Firebase\Auth;

    // $signInResult = $auth->signIn*()

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

IdP (Identitiy Provider) credentials are credentials provided by authentication providers other than Firebase,
for example Facebook, Github, Google or Twitter. You can find the currently supported authentication providers
in the constants of `https://github.com/kreait/firebase-php/blob/master/src/Firebase/Value/Provider.php <https://github.com/kreait/firebase-php/blob/master/src/Firebase/Value/Provider.php>`_

This could be useful if you already have "Sign in with Twitter" implemented in your application, and want to
authenticate the same user with Firebase.

Once you have received those credentials, you can use them to sign a user in with them:

.. code-block:: php

    // with an access token from Facebook
    $signInResult = $auth->signInWithFacebookAccessToken($accessToken);

    // with an ID token from Google
    $signInResult = $auth->signInWithGoogleIdToken($idToken);

    // with a Twitter OAuth 1.0 credential
    $signInResult = $auth->signInWithTwitterOauthCredential($accessToken, $oauthTokenSecret);


If you're using a different identity provider, you can use:

.. code-block:: php

    $signInResult = $auth->signInWithIdpAccessToken($provider, $accessToken);

    $signInResult = $auth->signInWithIdpIdToken($provider, $idToken);


Sign In without a token
-----------------------

.. code-block:: php

    $signInResult = $auth->signInAsUser($userOrUid, array $claims = null);


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
