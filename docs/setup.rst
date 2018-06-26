#####
Setup
#####

**********************
Google Service Account
**********************

In order to access a Firebase project using a server SDK, you must authenticate your requests to Firebase with
a `Service Account <https://developers.google.com/identity/protocols/OAuth2ServiceAccount>`_.

Follow the steps described in the official Firebase documentation to create a Service Account for your Firebase
application (see
`Add the Firebase Admin SDK to your Server <https://firebase.google.com/docs/admin/setup#add_firebase_to_your_app>`_)
and make sure the Service Account has the `Project -> Editor` or `Project -> Owner` role.

With autodiscovery
==================

By default, the SDK is able to autodiscover the Service Account for your project in the following conditions:

#. The path to the JSON key file is defined in one of the following environment variables

   * ``FIREBASE_CREDENTIALS``
   * ``GOOGLE_APPLICATION_CREDENTIALS``

#. The JSON Key file is located in Google's "well known path"

   * on Linux/MacOS: ``$HOME/.config/gcloud/application_default_credentials.json``
   * on Windows: ``$APPDATA/gcloud/application_default_credentials.json``

If one of the conditions above is met, creating a new Firebase instance is as easy as this:

.. code-block:: php

    use Kreait\Firebase\Factory;

    $firebase = (new Factory)->create();

A more explicit alternative:

.. code-block:: php

    use Kreait\Firebase\Factory;
    use Kreait\Firebase\ServiceAccount;

    $serviceAccount = ServiceAccount::discover();

    $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->create();


Manually
========

You can also pass the path to the Service Account JSON file explicitly:

.. code-block:: php

    use Kreait\Firebase\Factory;
    use Kreait\Firebase\ServiceAccount;

    $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/firebase_credentials.json');
    $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->create();


Use your own autodiscovery
==========================

You can use your own, custom autodiscovery methods as well:

.. code-block:: php

    use Kreait\Firebase\Factory;
    use Kreait\Firebase\ServiceAccount\Discoverer

    $discoverer = new Discoverer([
        function () {
            $serviceAccount = ...; // Instance of Kreait\Firebase\ServiceAccount

            return $serviceAccount;
        }
    ]);

    $firebase = (new Factory)
        ->withServiceAccountDiscoverer($myDiscoverer)
        ->create();


*******************
Custom Database URI
*******************

If the project ID in the JSON file does not match the URL of your Firebase application, or if you want to
be explicit, you can configure the Factory like this:

.. code-block:: php

    use Kreait\Firebase\Factory;

    $firebase = (new Factory)
        ->withDatabaseUri('https://my-project.firebaseio.com')
        ->create();

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

    $firebase = (new Factory)
        ->withHttpClientConfig($httpConfig)
        ->withHttpClientMiddlewares($httpMiddlewares)
        ->create();
