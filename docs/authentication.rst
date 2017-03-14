##############
Authentication
##############

In order to access a Firebase project using a server SDK, you must authenticate your server with Firebase. This
can be done either by creating and using a
`Service Account <https://developers.google.com/identity/protocols/OAuth2ServiceAccount>`_ (strongly
recommended), or by providing using a database secret (not recommended).

*****************************
With a Google Service Account
*****************************

Follow the steps described in the official Firebase documentation to create a Service Account for your Firebase
application: `Add Firebase to your app <https://firebase.google.com/docs/server/setup#add_firebase_to_your_app>`_.

You can now create a new Firebase instance with ``Firebase::fromServiceAccount($value)`` which accepts one of the
following values:

- the path to a Google Service Account JSON configuration file (recommended)
- a JSON string
- an array
- an instance of ``Firebase\ServiceAccount``

.. code-block:: php

    $firebase = Firebase::fromServiceAccount(__DIR__.'/google-service-account.json');

If the project ID in the JSON file does not match the URL of your Firebase application, or if you want to
be explicit, you can specify the Database URI either as a second parameter or an additional method call:

.. code-block:: php

    $firebase = Firebase::fromServiceAccount(
        __DIR__.'/google-service-account.json',
        'https://my-project.firebaseio.com'
    );

    $firebase = Firebase::fromServiceAccount(__DIR__.'/google-service-account.json');
        ->withDatabaseUri('https://my-project.firebaseio.com');


**********************
With a Database secret
**********************

.. note::

    Authenticating with a database secret has been officially deprecated since November 2016 and will
    be removed from this library as soon as Firebase doesn't accept it anymore.

You can create and retrieve Database secrets in the
`Service Accounts <https://console.firebase.google.com/project/_/settings/serviceaccounts/adminsdk>`_
tab in your project's settings page.

.. code-block:: php

    $secret = '...';
    $databaseUri = 'https://my-project-id.firebaseio.com';

    $firebase = Firebase::fromDatabaseUriAndSecret($databaseUri, $secret);

.. note::
    This is a legacy authentication method, you will only be able to access the Firebase Realtime Database
    when using it. If you want to access the Storage or other parts of your Firebase project, you will
    have to use Service account authentication.

*******************
Impersonating users
*******************

You can impersonate users of your Firebase application through the the ``asUserWithClaims()`` method, which requires
a user id as the first parameter, and an optional array with claims as the second.

.. code-block:: php

    $firebase = Firebase::fromServiceAccount(...);

    $authenticated = $firebase->asUserWithClaims('a-user-id', [
        'premium-user' => true
    ]);

*******************
Working with Tokens
*******************

If you need to create `Custom Tokens <https://firebase.google.com/docs/auth/server/create-custom-tokens>`_
or verify `ID Tokens <https://firebase.google.com/docs/auth/admin/verify-id-tokens>`_, a Service Account
based Firebase instance provides the ``getTokenHandler()`` method:

.. code-block:: php

    $firebase = Firebase::fromServiceAccount(...);

    $tokenHandler = $firebase->getTokenHandler();

    $uid = 'a-uid';
    $claims = ['foo' => 'bar']; // optional

    // Returns a Lcobucci\JWT\Token instance
    $customToken = $tokenHandler->createCustomToken($uid, $claims);
    echo $customToken; // "eyJ0eXAiOiJKV1..."

    $idTokenString = 'eyJhbGciOiJSUzI1...';
    // Returns a Lcobucci\JWT\Token instance
    $idToken = $tokenHandler->verifyIdToken($idTokenString);

    $uid = $idToken->getClaim('sub');

    echo $uid; // 'a-uid'

.. note::
    A standalone version of the Token Handler is available with the
    `kreait/firebase-tokens <https://packagist.org/packages/kreait/firebase-tokens>`_ library.
