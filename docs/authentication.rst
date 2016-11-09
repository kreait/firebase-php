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

***********************************************
Authentication overrides (a.k.a. Custom Tokens)
***********************************************

You can impersonate users of your Firebase application through the the ``asUserWithClaims()`` method, which requires
a user id as the first parameter, and an optional array with claims as the second.

.. code-block:: php

    $firebase = Firebase::fromServiceAccount(...);

    $authenticated = $firebase->asUserWithClaims('a-user-id', [
        'premium-user' => true
    ]);

If you want to be more explicit, you can also override the authentication just on a database connection:

.. code-block:: php

    // Using a service account (notice the V3 namespace part)
    $firebase = Firebase::fromServiceAccount(...);

    $auth = new \Firebase\V3\Auth\CustomToken('a-user-id', [
        'premium-user' => true
    ]);

    $database = $firebase
        ->getDatabase()
        ->withCustomAuth($auth);

    // Using a database secret (notice the V2 namespace part)
    $firebase = Firebase::fromDatabaseUriAndSecret($uri, $secret);

    $auth = new \Firebase\V2\Auth\CustomToken('a-user-id', [
        'premium-user' => true
    ]);

    $database = $firebase
        ->getDatabase()
        ->withCustomAuth($auth);

.. note::
    Under the hood, the SDK creates a
    `Custom Token <https://firebase.google.com/docs/auth/server/create-custom-tokens>`_ and uses to apply
    the `Security rules <https://firebase.google.com/docs/database/security/>`_ to the connection.

    Authentication overrides are performed differently, depending on whether you authenticate with a
    Google Service Account or a database secret.
