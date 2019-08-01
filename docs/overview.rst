########
Overview
########

************
Requirements
************

* PHP >= 7.0
* The `mbstring PHP extension <http://php.net/manual/en/book.mbstring.php>`_
* A Firebase project - create a new project in the `Firebase console <https://firebase.google.com/console/>`_,
  if you don't already have one.
* A Google service account, follow the instructions in the
  `official Firebase Server documentation <https://firebase.google.com/docs/server/setup#add_firebase_to_your_app>`_
  and place the JSON configuration file somewhere in your project's path.

************
Installation
************

The recommended way to install the Firebase Admin SDK is with
`Composer <http://getcomposer.org>`_. Composer is a dependency management tool
for PHP that allows you to declare the dependencies your project needs and
installs them into your project.

.. code-block:: bash

    composer require kreait/firebase-php ^4.28


Alternatively, you can specify the Firebase Admin SDK as a dependency in your
project's existing composer.json file:

.. code-block:: js

    {
      "require": {
         "kreait/firebase-php": "^4.28"
      }
   }

After installing, you need to require Composer's autoloader:

.. code-block:: php

    <?php

    require __DIR__.'/vendor/autoload.php';

You can find out more on how to install Composer, configure autoloading, and
other best-practices for defining dependencies at
`getcomposer.org <http://getcomposer.org>`_.

Please continue to the :ref:`Setup section <setup>` to learn more about connecting your application to Firebase.

*************
Usage example
*************

You can find more usage examples at
`https://github.com/jeromegamez/firebase-php-examples <https://github.com/jeromegamez/firebase-php-examples>`_
and in the `tests directory <https://github.com/kreait/firebase-php/tree/master/tests>`_
of this project's `GitHub repository <https://github.com/kreait/firebase-php/>`_.

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

**************
Issues/Support
**************

- For bugs, feature requests and past issues: `Github issue tracker <https://github.com/kreait/firebase-php/issues/>`_
- For help with and discussion about the PHP SDK: `Discord channel dedicated to this library <https://discord.gg/nbgVfty>`_
- For questions about Firebase in general: `Stack Overflow <https://stackoverflow.com/questions/tagged/firebase>`_ and the `Firebase Slack Community <https://firebase.community>`_.


*******
License
*******

Licensed using the `MIT license <http://opensource.org/licenses/MIT>`_.

    Copyright (c) Jérôme Gamez <https://github.com/jeromegamez> <jerome@gamez.name>

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.

************
Contributing
************

Guidelines
==========

#. The SDK utilizes PSR-1, PSR-2, PSR-4, and PSR-7.
#. This SDK has a minimum PHP version requirement of PHP 7.0. Pull requests must
   not require a PHP version greater than PHP 7.0 unless the feature is only
   utilized conditionally.
#. All pull requests must include unit tests to ensure the change works as
   expected and to prevent regressions.

Running the tests
=================

The SDK is unit tested with PHPUnit. Run the tests using the Makefile:

.. code-block:: bash

    make tests

Coding standards
================

The SDK uses the `PHP Coding Standars Fixer <https://github.com/FriendsOfPHP/PHP-CS-Fixer>`_
to ensure a uniform coding style. Apply coding standard fixed using the Makefile:

.. code-block:: bash

    make cs

from the root of the project.
