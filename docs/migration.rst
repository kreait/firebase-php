#########
Migration
#########

**********
3.1 to 3.2
**********

.. rubric:: Kreait\Firebase::getTokenHandler() has been deprecated

Use ``Kreait\Firebase\Auth::createCustomToken()`` and ``Kreait\Firebase\Auth::verifyIdToken()`` instead.

.. code-block:: php

    # Before
    $tokenHandler = $firebase->getTokenHandler();

    $tokenHandler->createCustomToken(...);
    $tokenHandler->verifyIdToken(...);

    # After
    $auth = $firebase->getAuth();

    $auth->createCustomToken(...);
    $auth->verifyIdToken(...);

**********
3.0 to 3.1
**********

.. rubric:: Kreait\Firebase\Factory::withCredentials() has been deprecated

.. code-block:: php

    # Before
    use Kreait\Firebase\Factory;

    $firebase = (new Factory)
        ->withCredentials(__DIR__.'/google-service-account.json');

    # After
    use Kreait\Firebase\Factory;
    use Kreait\Firebase\ServiceAccount;

    $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/google-service-account.json');
    $firebase = (new Firebase\Factory)
        ->withServiceAccount($serviceAccount);


**********
2.x to 3.0
**********

.. rubric:: Database secret authentication

As Database Secret based authentication has been deprecated by Firebase, it has been removed from this library.
Use Service Account based authentication instead.

.. rubric:: Firebase Factory

Previously, it was possible to create a new Firebase instance with a convenience class in the root namespace.
This class has been removed, and ``Kreait\Firebase\Factory`` is used instead:

.. code-block:: php

    # Before
    $firebase = \Firebase::fromServiceAccount('/path/to/google-service-account.json');

    # After
    use Kreait\Firebase\Factory;

    $firebase = (new Factory())
        ->withCredentials('/path/to/google-service-account.json')
        ->create();

.. rubric:: Changed namespace

All classes have been moved from the ``Firebase`` root namespace to ``Kreait\Firebase``
to avoid conflicts with official Firebase PHP libraries using this namespace.
