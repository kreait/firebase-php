.. title:: Firebase Admin SDK for PHP

##########################
Firebase Admin SDK for PHP
##########################

This Admin SDK makes it easy to interact with `Google Firebase <https://firebase.google.com>`_
from PHP applications.

The source code can be found at https://github.com/kreait/firebase-php/

.. code-block:: php

    $firebase = Firebase::fromServiceAccount(__DIR__.'/google-service-account.json')
    $database = $firebase->getDatabase();

    $newPost = $database
        ->getReference('blog/posts')
        ->push([
            'title' => 'Post title',
            'body' => 'This should probably be longer.'
        ]);

    $newPost->getKey(); // => -KVr5eu8gcTv7_AHb-3-
    $newPost->getUri(); // => https://my-project.firebaseio.com/blog/posts/-KVr5eu8gcTv7_AHb-3-

    $newPost->getChild('title')->set('Changed post title');
    $newPost->getValue(); // Fetches the data from the realtime database
    $newPost->remove();


**********
User Guide
**********

.. toctree::
    :maxdepth: 3

    overview
    authentication
    realtime-database

