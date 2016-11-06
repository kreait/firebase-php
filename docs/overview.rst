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

The recommended way to install the Firebase SDK is with
`Composer <http://getcomposer.org>`_. Composer is a dependency management tool
for PHP that allows you to declare the dependencies your project needs and
installs them into your project.

.. code-block:: bash

    # Install Composer
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"

You can add the Firebase SDK as a dependency using the composer.phar CLI:

.. code-block:: bash

    php composer.phar require kreait/firebase-php ^2.0


Alternatively, you can specify the Firebase SDK as a dependency in your
project's existing composer.json file:

.. code-block:: js

    {
      "require": {
         "kreait/firebase-php": "^2.0"
      }
   }

After installing, you need to require Composer's autoloader:

.. code-block:: php

    require 'vendor/autoload.php';

You can find out more on how to install Composer, configure autoloading, and
other best-practices for defining dependencies at
`getcomposer.org <http://getcomposer.org>`_.

**************
Issues/Support
**************

- `Github issue tracker <https://github.com/kreait/firebase-php/issues/>`_
- Join the Firebase Community Slack at https://firebase-community.appspot.com, join the #php channel and look
  for @jeromegamez.

*******
Roadmap
*******

The following planned features are not in a particular order:

- Integration of `Firebase Storage <https://firebase.google.com/docs/storage/>`_
- Automatic updates of `Firebase Rules <https://firebase.google.com/docs/database/security/>`_

  - Background:
    `Data must be indexed to be queriable or sortable <https://firebase.google.com/docs/database/security/indexing-data>`_.
    If you try to query a yet unindexed dataset, the Firebase REST API will return an error. With this feature, the
    SDK could execute an error, and if an error occurs, update the Firebase Rules as needed and retry.

- Support for listening to the
  `Firebase event stream <https://firebase.google.com/docs/reference/rest/database/#section-streaming>`_
- PHP Object Serialization and Deserialization
- Use parallel requests where possible to speed up operations

*******
License
*******

Licensed using the `MIT license <http://opensource.org/licenses/MIT>`_.

    Copyright (c) 2016 Jérôme Gamez <https://github.com/jeromegamez> <jerome@gamez.name>

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



****************
Acknowledgements
****************

* The structure and wording of this documentation is loosely based on the
  official Firebase documentation at `<https://firebase.google.com/docs/>`_.
* The index and overview page are adapted from
  `Guzzle's documentation <http://guzzle.readthedocs.io/en/latest/>`_.
