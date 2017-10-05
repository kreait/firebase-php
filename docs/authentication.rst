##############
Authentication
##############

*******************
Impersonating users
*******************

You can impersonate users of your Firebase application through the the ``asUserWithClaims()`` method, which requires
a user id as the first parameter, and an optional array with claims as the second.

.. code-block:: php

    use Kreait\Firebase\Factory;
    use Kreait\Firebase\ServiceAccount;

    $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/google-service-account.json');
    $apiKey = '<Firebase Web API key>';

    $firebase = (new Factory)
        ->withServiceAccountAndApiKey($serviceAccount, $apiKey)
        ->create();

    $user = $firebase->getAuth()->getUser('a-user-id');
    // You can also set claims for the given user
    $user = $firebase->getAuth()->getUser('a-user-id', ['premium-user' => true]);

    $authenticated = $firebase->asUser($user);

****************************
Working with Tokens directly
****************************

If you need to create `Custom Tokens <https://firebase.google.com/docs/auth/server/create-custom-tokens>`_
or verify `ID Tokens <https://firebase.google.com/docs/auth/admin/verify-id-tokens>`_, a Service Account
based Firebase instance provides the ``getTokenHandler()`` method:

.. code-block:: php

    use Kreait\Firebase;

    $firebase = (new Firebase\Factory())->create();

    $auth = $firebase->getAuth();

    $uid = 'a-uid';
    $claims = ['foo' => 'bar']; // optional

    // Returns a Lcobucci\JWT\Token instance
    $customToken = $auth->createCustomToken($uid, $claims);
    echo $customToken; // "eyJ0eXAiOiJKV1..."

    $idTokenString = 'eyJhbGciOiJSUzI1...';
    // Returns a Lcobucci\JWT\Token instance
    $idToken = $auth->verifyIdToken($idTokenString);

    $uid = $idToken->getClaim('sub');

    echo $uid; // 'a-uid'

.. note::
    A standalone version of the Token Handler is available with the
    `kreait/firebase-tokens <https://packagist.org/packages/kreait/firebase-tokens>`_ library.
