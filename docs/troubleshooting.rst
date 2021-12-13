###############
Troubleshooting
###############

.. note::
    This SDK works with immutable objects until noted otherwise. You can recognize these
    objects when they have a ˚`with*``method. In that case, please keep in mind that in
    order to get hold of the changes you made, you will have to use the result of
    that method, e.g. ``$changedObject = $object->withChangedProperty();``.

**************
Error handling
**************

In general, if executing a method from the SDK doesn't throw an error, it is safe to assume that the
requested operation has worked according to the motto "no news is good news". If you do get an error,
it is good practice to wrap the problematic code in a try/catch (*try* an operation and handle
possible errors by *catch* ing them):

.. code-block:: php

    use Kreait\Firebase\Exception\FirebaseException;
    use Throwable;

    try {
        // The operation you want to perform
        echo 'OK';
    } catch (FirebaseException $e} {
        echo 'An error has occurred while working with the SDK: '.$e->getMessage;
    } catch (Throwable $e) {
        echo 'A not-Firebase specific error has occurred: '.$e->getMessage;
    }

This is especially useful when you encounter ``Fatal error: Uncaught GuzzleHttp\Exception\ClientException``
errors which are caused by the Google/Firebase APIs rejecting a request. Those errors are handled by the
SDK and should be converted to instances of ``Kreait\Firebase\Exception\FirebaseException``.

If you want to be sure to catch *any* error, catch ``Throwable``.

************************************
Call to private/undefined method ...
************************************

If you receive an error like

.. code-block:: bash

    Fatal error: Uncaught Error: Call to private method Kreait\Firebase\ServiceAccount::fromJsonFile()

you have most likely followed a tutorial that is targeted at Version 4.x of this release and have code
that looks like this:

.. code-block:: php

    $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/google-service-account.json');
    $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->create();

    $database = $firebase->getDatabase();

Change it to the following:

.. code-block:: php

    $factory = (new Factory)->withServiceAccount(__DIR__.'/google-service-account.json');

    $database = $factory->createDatabase();

********************************
PHP Parse Error/PHP Syntax Error
********************************

If you're getting an error in the likes of

.. code-block:: bash

    PHP Parse error: syntax error, unexpected ':', expecting ';' or '{' in ...

the environment you are running the script in does not use PHP 7.x. You can check this
by adding the line

.. code-block:: php

    echo phpversion(); exit;

somewhere in your script.

****************************************
Class 'Kreait\\Firebase\\ ...' not found
****************************************

You are probably not using the latest release of the SDK, please update your composer dependencies.

*********************************************
Call to undefined function ``openssl_sign()``
*********************************************

You need to install the OpenSSL PHP Extension: http://php.net/openssl

********************************************
Default sound not played on message delivery
********************************************

If you specified ``'sound' => 'default'`` in the message payload, try chaning it
to ``'sound' => "default"`` - although single or double quotes shouldn't™ make
a difference, `it has been reported that this can solve the issue <https://github.com/kreait/firebase-php/issues/454#issuecomment-706771776>`_.

******************
cURL error XX: ...
******************

If you receive a ``cURL error XX: ...``, make sure that you have a current
CA Root Certificates bundle on your system and that PHP uses it.

To see where PHP looks for the CA bundle, check the output of the
following command:

.. code-block:: php

    var_dump(openssl_get_cert_locations());

which should lead to an output similar to this:

.. code-block:: php

    array(8) {
        'default_cert_file' =>
        string(32) "/usr/local/etc/openssl/cert.pem"
        'default_cert_file_env' =>
        string(13) "SSL_CERT_FILE"
        'default_cert_dir' =>
        string(29) "/usr/local/etc/openssl/certs"
        'default_cert_dir_env' =>
        string(12) "SSL_CERT_DIR"
        'default_private_dir' =>
        string(31) "/usr/local/etc/openssl/private"
        'default_default_cert_area' =>
        string(23) "/usr/local/etc/openssl"
        'ini_cafile' =>
        string(0) ""
        'ini_capath' =>
        string(0) ""
    }

Now check if the file given in the ``default_cert_file`` field actually exists.
Create a backup of the file, download the current CA bundle from
https://curl.haxx.se/ca/cacert.pem and put it where ``default_cert_file``
points to.

If the problem still occurs, another possible solution is to configure the ``curl.cainfo``
setting in your ``php.ini``:

.. code-block:: ini

    [curl]
    curl.cainfo = /absolute/path/to/cacert.pem

**********************
"403 Forbidden" Errors
**********************

Under the hood, a Firebase project is actually a Google Cloud project with pre-defined and pre-allocated
permissions and resources.

When Google adds features to its product line, it is possible that you have to manually configure your
Firebase/Google Cloud Project to take advantage of those new features.

When a request to the Firebase APIs fails, please make sure that the according Google Cloud API is
enabled for your project:

- Firebase Services: https://console.cloud.google.com/apis/library/firebase.googleapis.com
- Cloud Messaging (FCM): https://console.cloud.google.com/apis/library/fcm.googleapis.com
- FCM Registration API: https://console.cloud.google.com/apis/library/fcmregistrations.googleapis.com
- Dynamic Links: https://console.cloud.google.com/apis/library/firebasedynamiclinks.googleapis.com
- Firestore: https://console.cloud.google.com/apis/library/firestore.googleapis.com
- Realtime Database Rules: https://console.cloud.google.com/apis/library/firebaserules.googleapis.com
- Remote Config: https://console.cloud.google.com/apis/library/firebaseremoteconfig.googleapis.com
- Storage: https://console.cloud.google.com/apis/library/storage-component.googleapis.com

Please also make sure that the Service Account you are using for your project has all necessary
roles and permissions as described in the official documentation at `Manage project access with Firebase IAM <https://firebase.google.com/docs/projects/iam/overview>`_.

*******************************
MultiCast SendReports are empty
*******************************

This is an issue seen in XAMPP/WAMP environments and seems related to the cURL version shipped with
the current PHP installation. Please ensure that cURL is installed with at least version **7.67**
(preferably newer, version 7.70 is known to work).

You can check the currently installed cURL version by adding the following line somewhere in your
code:

.. code-block:: php

    echo curl_version()['version']; exit;

To install a newer version of cURL, download the latest release from https://curl.haxx.se/ . From
the unpacked archive in the ``bin`` folder, use the file ending with ``libcurl*.dll`` to overwrite
the existing ``libcurl*.dll`` in the ``ext`` folder of your PHP installation and restart the
environment.

If this issue occurs in other environments (e.g. Linux or MacOS), please ensure that you have the
latest (minor) versions of PHP and cURL installed. If the problem persists, please open an issue
in the issue tracker.

*******************
Proxy configuration
*******************

If you need to access the Firebase/Google APIs through a proxy, you can specify an according
HTTP Client option while configuring the service factory: :ref:`http-client-options`

*********
Debugging
*********

In order to debug HTTP requests to the Firebase/Google APIs, you can enable the factory's
debug mode and provide an instance of ``Psr\Log\LoggerInterface``. HTTP requests and
responses will then be pushed to this logger with their full headers and bodies.

.. code-block:: php

    $factory = $factory->withHttpDebugLogger($logger);

If you want to make sure that the Factory has the configuration you expect it to have,
call the ``getDebugInfo()`` method:

.. code-block:: php

    $factoryInfo = $factory->getDebugInfo();

The output will be something like this:

.. code-block::

    Array
    (
        [credentialsType] => Google\Auth\Credentials\ServiceAccountCredentials
        [databaseUrl] => https://project-id-default-rtdb.firebaseio.com
        [defaultStorageBucket] =>
        [projectId] => project-id
        [serviceAccount] => Array
            (
                [type] => service_account
                [project_id] => project-id
                [private_key_id] => a1b2c3d4e5f6g7h8i9j0
                [private_key] => {exists, redacted}
                [client_email] => project-id-xyz@beste-firebase.iam.gserviceaccount.com
                [client_id] => 1234567890987654321
                [auth_uri] => https://accounts.google.com/o/oauth2/auth
                [token_uri] => https://oauth2.googleapis.com/token
                [auth_provider_x509_cert_url] => https://www.googleapis.com/oauth2/v1/certs
                [client_x509_cert_url] => https://www.googleapis.com/robot/v1/metadata/x509/project-id-xyz%40beste-firebase.iam.gserviceaccount.com
            )

        [tenantId] =>
        [tokenCacheType] => Google\Auth\Cache\MemoryCacheItemPool
        [verifierCacheType] => Firebase\Auth\Token\Cache\InMemoryCache
    )

The private key of a service account will be redacted.
