###############
Troubleshooting
###############

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

    try {
        return $firebase->getAuth()->verifyIdToken($idTokenString);
    } catch (IssuedInTheFuture $e) {
        return $e->getToken();
    } catch (InvalidIdToken $e) {
        echo $e->getMessage();
        exit;
    }
