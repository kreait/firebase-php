.. _setup:

#####
Setup
#####

**********************
Google Service Account
**********************

In order to access a Firebase project using a server SDK, you must authenticate your requests to Firebase with
a `Service Account <https://developers.google.com/identity/protocols/OAuth2ServiceAccount>`_.

Follow the steps described in the official Firebase documentation to create a Service Account for your Firebase
application:
`Add the Firebase Admin SDK to your Server <https://firebase.google.com/docs/admin/setup#add_firebase_to_your_app>`_.

You can then configure the SDK to use this Service Account:

**With the SDK**

.. code-block:: php

    use Kreait\Firebase\Factory;

    $factory = (new Factory)->withServiceAccount('/path/to/firebase_credentials.json');

**With the** `Symfony Bundle <https://github.com/kreait/firebase-bundle>`_

Please see `https://github.com/kreait/firebase-bundle#configuration <https://github.com/kreait/firebase-bundle#configuration>`_

**With the** `Laravel/Lumen Package <https://github.com/kreait/laravel-firebase>`_

Please see `https://github.com/kreait/laravel-firebase#configuration <https://github.com/kreait/laravel-firebase#configuration>`_

With autodiscovery
==================

The SDK is able to autodiscover the Service Account for your project in the following conditions:

#. Your application runs on Google Cloud Engine.

#. The path to the JSON key file is defined in one of the following environment variables

   * ``FIREBASE_CREDENTIALS``
   * ``GOOGLE_APPLICATION_CREDENTIALS``

#. The JSON Key file is located in Google's "well known path"

   * on Linux/MacOS: ``$HOME/.config/gcloud/application_default_credentials.json``
   * on Windows: ``$APPDATA/gcloud/application_default_credentials.json``

If you want to use autodiscovery, a Service Account must not be explicitly configured.


*******************
Custom Database URI
*******************

.. note::
    It is not necessary to define a custom database URI in most cases.

If the project ID in the JSON file does not match the URL of your Firebase application, or if you want to
be explicit, you can configure the Factory like this:

.. code-block:: php

    use Kreait\Firebase\Factory;

    $factory = (new Factory())
        ->withDatabaseUri('https://my-project.firebaseio.com');

*******
Caching
*******

Authentication tokens
---------------------

Before connecting to the Firebase APIs, the SDK fetches an authentication token for your credentials.
This authentication token is cached in-memory so that it can be re-used during the same process.

If you want to cache authentication tokens more effectively, you can provide any
`implementation of psr/cache <https://packagist.org/providers/psr/cache-implementation>`_ to the
Firebase factory when creating your Firebase instance.

.. note::
    Authentication tokens are cached in-memory by default. For Symfony and Laravel,
    the Framework's cache will automatically be used.

For Symfony and Laravel, the Framework's cache will automatically be used.

Here is an example using the `Symfony Cache Component <https://symfony.com/doc/current/components/cache.html>`_:

.. code-block:: php

        use Symfony\Component\Cache\Simple\FilesystemCache;

        $factory = $factory->withAuthTokenCache(new FilesystemCache());


ID Token Verification
---------------------

In order to verify ID tokens, the verifier makes a call to fetch Firebase's currently available public keys.
The keys are cached in memory by default.

If you want to cache the public keys more effectively, you can provide any
`implementation of psr/simple-cache <https://packagist.org/providers/psr/simple-cache-implementation>`_ to the
Firebase factory when creating your Firebase instance.

.. note::
    Public keys tokens are cached in-memory by default. For Symfony and Laravel,
    the Framework's cache will automatically be used.

Here is an example using the `Symfony Cache Component <https://symfony.com/doc/current/components/cache.html>`_:

.. code-block:: php

        use Symfony\Component\Cache\Simple\FilesystemCache;

        $factory = $factory->withVerifierCache(new FilesystemCache());

********************
End User Credentials
********************

.. note::
    While theoretically possible, it's not recommended to use end user credentials in the context
    of a Server-to-Server backend application.

When using End User Credentials (for example if you set you application default credentials locally
with ``gcloud auth application-default login``), you need to provide the ID of the project you
want to access directly and suppress warnings triggered by the Google Auth Component:

.. code-block:: php

    use Kreait\Firebase\Factory;

    putenv('SUPPRESS_GCLOUD_CREDS_WARNING=true');

    // This will use the project defined in the Service Account
    // credentials files by default
    $base = (new Factory())->withProjectId('firebase-project-id');
