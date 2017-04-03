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

Download the Service Account JSON Key to one of the following locations:

#. to the path defined by the environment variable ``FIREBASE_CREDENTIALS``
#. to the path defined by the environment variable ``GOOGLE_APPLICATION_CREDENTIALS``
#. to Google's "well known path"

   * on Linux/MacOS: ``$HOME/.config/gcloud/application_default_credentials.json``
   * on Windows: ``$APPDATA/gcloud/application_default_credentials.json``

#. to any other path your project has access to

.. code-block:: php

    # If the JSON file is in one of the known paths, the factory will
    # find it automatically
    $firebase = (new \Firebase\Factory())->create();

    # If the JSON file is located in a path accessible to your project,
    # or if you want to create multiple dedicated instances
    $firebase = (new \Firebase\Factory())
        ->withCredentials(__DIR__.'/path/to/google-service-account.json')
        ->create();

If the project ID in the JSON file does not match the URL of your Firebase application, or if you want to
be explicit, you can configure the Factory like this:

.. code-block:: php

    $firebase = (new \Firebase\Factory())
        ->withCredentials(__DIR__.'/path/to/google-service-account.json')
        ->withDatabaseUri('https://my-project.firebaseio.com')
        ->create();


***********************************
With a Database secret (Deprecated)
***********************************

.. note::

    Authenticating with a database secret has been officially deprecated since November 2016 and will
    be removed from this library in Release 3.0.

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

    $firebase = (new \Firebase\Factory())->create();

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

    $firebase = (new \Firebase\Factory())->create();

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

If you want to use a custom token handler, you can do so by passing it to the factory:

.. code-block:: php

    $handler = new \Firebase\Auth\Token\Handler(...);

    $firebase = (new \Firebase\Factory())
        ->withTokenHandler($handler);
        ->create();

.. note::
    A standalone version of the Token Handler is available with the
    `kreait/firebase-tokens <https://packagist.org/packages/kreait/firebase-tokens>`_ library.
