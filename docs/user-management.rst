###############
User management
###############

You can enable user management features by providing your project's web API key
to the Firebase factory and getting an ``Auth`` instance:

.. code-block:: php

    use Kreait\Firebase\Factory;
    use Kreait\Firebase\ServiceAccount;

    $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/google-service-account.json');
    $apiKey = '<Firebase Web API key>';

    $firebase = (new Factory)
        ->withServiceAccountAndApiKey($serviceAccount, $apiKey)
        ->create();

    $auth = $firebase->getAuth();

**************************
Creating an anonymous user
**************************

.. code-block:: php

    $user = $auth->createAnonymousUser();
    $anonymousConnection = $firebase->asUser($user);

***************************************
Creating a user with email and password
***************************************

.. code-block:: php

    $user = $auth->createUserWithEmailAndPassword('user@domain.tld', 'a secure password');
    $userConnection = $firebase->asUser($user);

*********************
Getting a user by UID
*********************

.. code-block:: php

    $user = $auth->getUser('some-uid');
    # Setting additional claims for the user
    $user = $auth->getUser('some-uid', ['premium-user' => true]);

    $userConnection = $firebase->asUser($user);

************************************
Getting a user by email and password
************************************

.. code-block:: php

    $user = $auth->getUserByEmailAndPassword('user@domain.tld', 'a password');
    $userConnection = $firebase->asUser($user);

**************************
Changing a user's password
**************************

.. code-block:: php

    $user = $auth->getUser('some-uid');
    $updatedUser = $auth->changeUserPassword($user, 'new password');

***********************
Changing a user's email
***********************

.. code-block:: php

    $user = $auth->getUser('some-uid');
    $updatedUser = $auth->changeUserEmail($user, 'user@domain.tld');

***************
Deleting a user
***************

.. code-block:: php

    $user = $auth->getUser('some-uid');
    $auth->deleteUser($user);

*************************************
Trigger email verification for a user
*************************************

.. code-block:: php

    $user = $auth->getUser('some-uid');
    $auth->sendEmailVerification($user);

***************************
Send a password reset email
***************************

.. code-block:: php

    // Using an email address only
    $email = 'user@domain.tld';
    $auth->sendPasswordResetEmail($email);

    // Using an already fetched user
    $user = $auth->getUser('some-uid');
    $auth->sendPasswordResetEmail($user);
