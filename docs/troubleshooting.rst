###############
Troubleshooting
###############

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

You are not using the latest release of the SDK, please update your composer dependencies.

*********************************************
Call to undefined function ``openssl_sign()``
*********************************************

You need to install the OpenSSL PHP Extension: http://php.net/openssl

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

**********************************
ID Tokens are issued in the future
**********************************

When ID Token verification fails because of an ``IssuedInTheFuture`` exception, this is an
indication that the system time in your environment is not set correctly.

If you chose to ignore the issue, you can catch the exception and return the ID token nonetheless:

.. code-block:: php

    use Firebase\Auth\Token\Exception\InvalidToken;
    use Firebase\Auth\Token\Exception\IssuedInTheFuture;

    $auth = $factory->createAuth();

    try {
        return $auth->verifyIdToken($idTokenString);
    } catch (IssuedInTheFuture $e) {
        return $e->getToken();
    } catch (InvalidIdToken $e) {
        echo $e->getMessage();
        exit;
    }

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
- Dynamic Links: https://console.cloud.google.com/apis/library/firebasedynamiclinks.googleapis.com
- Firestore: https://console.cloud.google.com/apis/library/firestore.googleapis.com
- Realtime Database Rules: https://console.cloud.google.com/apis/library/firebaserules.googleapis.com
- Remote Config: https://console.cloud.google.com/apis/library/firebaseremoteconfig.googleapis.com
- Storage: https://console.cloud.google.com/apis/library/storage-component.googleapis.com

*******************
Proxy configuration
*******************

If you need to access the Firebase/Google APIs through a proxy, you can configure the SDK to use one via
`Guzzle's proxy configuration <http://docs.guzzlephp.org/en/stable/request-options.html#proxy>`_:

.. code-block:: php

    use Kreait\Firebase\Factory;

    $factory = (new Factory())
        ->withHttpClientConfig([
            'proxy' => 'tcp://<host>:<port>'
        ]);

**********************
Debugging API requests
**********************

In order to debug HTTP requests to the Firebase/Google APIs, you can set
`Guzzle's debug option <http://docs.guzzlephp.org/en/stable/request-options.html#debug>`_ to ``true`` in the
HTTP client config:

.. code-block:: php

    use Kreait\Firebase\Factory;

    $factory = (new Factory())
        ->withHttpClientConfig([
            'debug' => true
        ]);
