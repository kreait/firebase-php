.. title:: Firebase PHP SDK

##############################
Firebase PHP SDK Documentation
##############################

.. image:: https://poser.pugx.org/kreait/firebase-php/v/stable
   :target: https://packagist.org/packages/kreait/firebase-php
.. image:: https://poser.pugx.org/kreait/firebase-php/downloads
   :target: https://packagist.org/packages/kreait/firebase-php
.. image:: https://travis-ci.org/kreait/firebase-php.svg?branch=master
   :target: https://travis-ci.org/kreait/firebase-php
.. image:: https://scrutinizer-ci.com/g/kreait/firebase-php/badges/quality-score.png?b=master
   :target: https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master
.. image:: https://scrutinizer-ci.com/g/kreait/firebase-php/badges/coverage.png?b=master
   :target: https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master

This SDK makes it easy to interact with `Google Firebase <https://firebase.google.com>`_
applications.

- Simple and fluent interface to work with References, Querys and Data snapshots
- Abstracts away the underlying communication with the Firebase REST API
- Uses official Google libraries where possible
- Supports authentication with a Google service account (V3) or a database secret (V2)
- Removes limitations of the REST API (e.g.
  `sorted results <https://firebase.google.com/docs/database/rest/retrieve-data#section-rest-ordered-data>`_)

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
