.. title:: Firebase PHP SDK

##############################
Firebase PHP SDK Documentation
##############################

This SDK makes it easy to interact with `Google Firebase <https://firebase.google.com>`_
applications.

.. note::
    This is a 3rd party SDK and not maintained or supported by Firebase or Google.

.. code-block:: php

    $firebase = Firebase::fromServiceAccount(__DIR__.'/google-service-account.json');

    $database = $firebase->getDatabase();

    $root = $database->getReference('/');

    $completeSnapshot = $root->getSnapshot();

    $root->getChild('users')->push([
        'username' => uniqid('user', true),
        'email' => uniqid('email', true).'@domain.tld'
    ]);

    $users = $database->getReference('users');

    $sortedUsers = $users
        ->orderByChild('username', SORT_DESC)
        ->limitToFirst(10)
        ->getValue(); // shortcut for ->getSnapshot()->getValue()

    $users->remove();


**********
User Guide
**********

.. toctree::
    :maxdepth: 2

    overview
    authentication
    realtime-database

