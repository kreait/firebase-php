.. title:: Firebase Admin SDK for PHP

##########################
Firebase Admin SDK for PHP
##########################

Interact with `Google Firebase <https://firebase.google.com>`_ from your PHP application.

The source code can be found at https://github.com/kreait/firebase-php/

.. code-block:: php

    <?php

    require __DIR__.'/vendor/autoload.php';

    use Kreait\Firebase\Factory;
    use Kreait\Firebase\ServiceAccount;

    // This assumes that you have placed the Firebase credentials in the same directory
    // as this PHP file.
    $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/google-service-account.json');

    $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        // The following line is optional if the project id in your credentials file
        // is identical to the subdomain of your Firebase project. If you need it,
        // make sure to replace the URL with the URL of your project.
        ->withDatabaseUri('https://my-project.firebaseio.com')
        ->create();

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
    setup
    realtime-database
    authentication
    user-management
    troubleshooting
