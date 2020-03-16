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

***********************************
HTTP Client Options and middlewares
***********************************

If you want to extend or change the behaviour of the underlying HTTP client, you can pass options to it
while creating your Firebase instance.

See `Guzzle Request Options <http://docs.guzzlephp.org/en/stable/request-options.html>`_ for the available options
and `Guzzle Middlewares <http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html#middleware>`_ for
information on how to use middlewares.

.. code-block:: php

    use Kreait\Firebase\Factory;

    $httpConfig = [
        // see http://docs.guzzlephp.org/en/stable/request-options.html
    ];

    $httpMiddlewares = [
        // see http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html#middleware
    ];

    $factory = (new Factory)
        ->withHttpClientConfig($httpConfig)
        ->withHttpClientMiddlewares($httpMiddlewares);

Using a custom HTTP Handler
===========================

By default, Guzzle will choose the most appropriate HTTP handler to perform HTTP requests. You can override the handler
by using

.. code-block:: php

    use Kreait\Firebase\Factory;
    use GuzzleHttp\Handler\MockHandler;
    use GuzzleHttp\Handler\StreamHandler;
    use GuzzleHttp\Handler\CurlHandler;
    use GuzzleHttp\Handler\CurlMultiHandler;

    // for example (or one of the above)
    $handler = new StreamHandler();

    $factory = (new Factory)
        ->withHttpClientConfig(['handler' => $handler]);
