########
Overview
########

************
Requirements
************

* PHP >= 7.2
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

If you want to use the SDK within a Framework, please follow the installation instructions here:

- **Laravel**: `kreait/laravel-firebase <https://github.com/kreait/laravel-firebase>`_
- **Symfony**: `kreait/firebase-bundle <https://github.com/kreait/firebase-bundle>`_

.. code-block:: bash

    composer require kreait/firebase-php

After installing, you need to require Composer's autoloader:

.. code-block:: php

    <?php

    require __DIR__.'/vendor/autoload.php';

You can find out more on how to install Composer, configure autoloading, and
other best-practices for defining dependencies at
`getcomposer.org <http://getcomposer.org>`_.

Please continue to the :ref:`Setup section <setup>` to learn more about connecting your application to Firebase.

**************
Usage examples
**************

You can find usage examples at
`https://github.com/jeromegamez/firebase-php-examples <https://github.com/jeromegamez/firebase-php-examples>`_
and in the `tests directory <https://github.com/kreait/firebase-php/tree/master/tests>`_
of this project's `GitHub repository <https://github.com/kreait/firebase-php/>`_.


**************
Issues/Support
**************

- For bugs and past issues: `Github issue tracker <https://github.com/kreait/firebase-php/issues/>`_
- For questions and general discussions: `GitHub discussions <https://github.com/kreait/firebase-php/discussions>`_
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
#. This SDK has a minimum PHP version requirement of PHP 7.2. Pull requests must
   not require a PHP version greater than PHP 7.2 unless the feature is only
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
