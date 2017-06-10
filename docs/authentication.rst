##############
Authentication
##############

*******************
Impersonating users
*******************

You can impersonate users of your Firebase application through the the ``asUserWithClaims()`` method, which requires
a user id as the first parameter, and an optional array with claims as the second.

.. code-block:: php

    use Kreait\Firebase;

    $firebase = (new Firebase\Factory())->create();

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

    use Kreait\Firebase;

    $firebase = (new Firebase\Factory())->create();

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

    use Kreait\Firebase;

    $handler = new Firebase\Auth\Token\Handler(...);

    $firebase = (new Firebase\Factory())
        ->withTokenHandler($handler);
        ->create();

.. note::
    A standalone version of the Token Handler is available with the
    `kreait/firebase-tokens <https://packagist.org/packages/kreait/firebase-tokens>`_ library.
