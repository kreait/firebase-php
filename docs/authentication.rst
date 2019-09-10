#####################
Authentication [#f1]_
#####################

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


**********************************
Authenticate with admin privileges
**********************************

When you initialize the Firebase Admin SDK with the credentials for a service account with the Editor role on
your Firebase project, that instance has complete read and write access to your project's Realtime Database.

.. code-block:: php

        use Kreait\Firebase\Factory;

        $firebase = (new Factory)
            ->withServiceAccount('/path/to/google-service-account.json')
            ->create();

.. note::
    Your service only has as much access as the service account used to authenticate it. For example, you can limit
    your service to read-only by using a service account with the Reader role on your project. Similarly, a
    service account with no role on the project is not able to read or write any data.

************************************
Authenticate with limited privileges
************************************

As a best practice, a service should have access to only the resources it needs.

To get more fine-grained control over the resources a Firebase app instance can access, use a unique
identifier in your `Security Rules <https://firebase.google.com/docs/database/security/>`_ to
represent your service.

Then set up appropriate rules which grant your service access to the resources it needs. For example:

.. code-block:: js

    {
      "rules": {
        "public_resource": {
          ".read": true,
          ".write": true
        },
        "some_resource": {
          ".read": "auth.uid === 'my-service-worker'",
          ".write": false
        },
        "another_resource": {
          ".read": "auth.uid === 'my-service-worker'",
          ".write": "auth.uid === 'my-service-worker'"
        }
      }
    }

Then, on your server, when you initialize the Firebase app, use the ``asUser($uid)`` method
with the identifier you used to represent your service in your Security Rules.

.. code-block:: php
   :emphasize-lines: 5

    use Kreait\Firebase\Factory;

    $firebase = (new Factory)
        ->withServiceAccount('/path/to/google-service-account.json')
        ->asUser('my-service-worker')
        ->create();


***************************
Create custom tokens [#f2]_
***************************

The Firebase Admin SDK has a built-in method for creating custom tokens. At a minimum, you need to provide a uid,
which can be any string but should uniquely identify the user or device you are authenticating.
These tokens expire after one hour.

.. code-block:: php

    $uid = 'some-uid';

    $customToken = $firebase->getAuth()->createCustomToken($uid);

You can also optionally specify additional claims to be included in the custom token. For example,
below, a premiumAccount field has been added to the custom token, which will be available in
the auth / request.auth objects in your Security Rules:

.. code-block:: php

    $uid = 'some-uid';
    $additionalClaims = [
        'premiumAccount' => true
    ];

    $customToken = $firebase->getAuth()->createCustomToken($uid, $additionalClaims);

    $customTokenString = (string) $customToken;

.. note::
    This library uses `lcobucci/jwt <https://github.com/lcobucci/jwt>`_ to work with JSON Web Tokens (JWT).
    You can find the usage instructions at
    `https://github.com/lcobucci/jwt/blob/3.2/README.md <https://github.com/lcobucci/jwt/blob/3.2/README.md>`_.


*********************************
Verify a Firebase ID Token [#f3]_
*********************************

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
        $verifiedIdToken = $firebase->getAuth()->verifyIdToken($idTokenString);
    } catch (InvalidToken $e) {
        echo $e->getMessage();
    }

    $uid = $verifiedIdToken->getClaim('sub');
    $user = $firebase->getAuth()->getUser($uid);

``Auth::verifyIdToken()`` accepts up to three parameters:

============================ ============ ===========
Parameter                    Type         Description
============================ ============ ===========
``idToken``                  string|Token **(required)** The ID token to verify
``checkIfRevoked``           boolean      (optional, default: ``false`` ) check if the ID token is revoked
``allowTimeInconsistencies`` boolean      (optional, default: ``false`` ) allow a token even if it's timestamps are invalid
============================ ============ ===========

.. warning::
    Allowing time inconsistencies might impose a security risk. Do this only when you are not able
    to fix your environment's time to be consistent with Google's servers. This parameter is here
    for backwards compatibility reasons, and will be removed in the next major version. You
    should not rely on it.

.. note::
    A leeway of 5 minutes is applied when verifying time based claims starting with release 4.25.0

.. note::
    This library uses `lcobucci/jwt <https://github.com/lcobucci/jwt>`_ to work with JSON Web Tokens (JWT).
    You can find the usage instructions at
    `https://github.com/lcobucci/jwt/blob/3.2/README.md <https://github.com/lcobucci/jwt/blob/3.2/README.md>`_.

Caching Google's public keys
----------------------------

In order to verify ID tokens, the verifier makes a call to fetch Firebase's currently available public keys.
The keys are cached in memory by default.

If you want to cache the public keys more effectively, you can provide any
`implementation of psr/simple-cache <https://packagist.org/providers/psr/simple-cache-implementation>`_ to the
Firebase factory when creating your Firebase instance.

Here is an example using the `Symfony Cache Component <https://symfony.com/doc/current/components/cache.html>`_:

.. code-block:: php

        use Kreait\Firebase\Factory;
        use Symfony\Component\Cache\Simple\FilesystemCache;

        $cache = new FilesystemCache();

        $firebase = (new Factory)
            ->withServiceAccount('/path/to/google-service-account.json')
            ->withVerifierCache($cache)
            ->create();

.. rubric:: References

.. [#f1] `Google: Introduction to the Admin Database API <https://firebase.google.com/docs/database/admin/start>`_
.. [#f2] `Google: Create custom tokens <https://firebase.google.com/docs/auth/admin/create-custom-tokens>`_
.. [#f3] `Google: Verify ID Tokens <https://firebase.google.com/docs/auth/admin/verify-id-tokens>`_
