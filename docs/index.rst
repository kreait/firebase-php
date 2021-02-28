.. title:: Firebase Admin SDK for PHP

##########################
Firebase Admin SDK for PHP
##########################

Interact with `Google Firebase <https://firebase.google.com>`_ from your PHP application.

.. image:: https://img.shields.io/github/license/kreait/firebase-php.svg
   :target: https://github.com/kreait/firebase-php/blob/master/LICENSE
   :alt: License
.. image:: https://img.shields.io/github/stars/kreait/firebase-php.svg
   :target: https://github.com/kreait/firebase-php/stargazers
   :alt: Stargazers
.. image:: https://img.shields.io/packagist/dt/kreait/firebase-php.svg
   :target: https://packagist.org/packages/kreait/firebase-php
   :alt: Total downloads
.. image:: https://img.shields.io/discord/807679292573220925.svg?color=7289da&logo=discord
   :target: https://discord.gg/nbgVfty
   :alt: Community chat
.. image:: https://img.shields.io/static/v1?logo=GitHub&label=Sponsor&message=%E2%9D%A4&color=ff69b4
   :target: https://github.com/sponsors/jeromegamez
   :alt: Sponsoring

.. note::
    If you are interested in using the PHP Admin SDK as a client for end-user access
    (for example, in a web application), as opposed to admin access from a
    privileged environment (like a server), you should instead follow the
    `instructions for setting up the client JavaScript SDK <https://firebase.google.com/docs/web/setup>`_.

The source code can be found at https://github.com/kreait/firebase-php/ .

***********
Quick Start
***********

.. code-block:: php

    use Kreait\Firebase\Factory;

    $factory = (new Factory)
        ->withServiceAccount('/path/to/firebase_credentials.json')
        ->withDatabaseUri('https://my-project-default-rtdb.firebaseio.com');

    $auth = $factory->createAuth();
    $realtimeDatabase = $factory->createDatabase();
    $cloudMessaging = $factory->createMessaging();
    $remoteConfig = $factory->createRemoteConfig();
    $cloudStorage = $factory->createStorage();
    $firestore = $factory->createFirestore();

**********
User Guide
**********

.. toctree::
    :maxdepth: 3

    overview
    setup
    cloud-messaging
    cloud-firestore
    cloud-storage
    realtime-database
    authentication
    user-management
    dynamic-links
    remote-config
    framework-integrations
    tutorials
    troubleshooting
