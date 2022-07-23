#############################
Testing and Local Development
#############################

*****************
Integration Tests
*****************

The most reliable way of testing your project is to create a separate Firebase project and configure your tests to
use it instead of the your production project. For example, you could have multiple Firebase projects depending on
your use-case:

    - ``my-project-dev``: used for developers while they develop new features
    - ``my-project-int``: use by CI/CD pipelines
    - ``my-project-staging``: used to present upcoming to stake holders
    - ``my-project``: used for production

*********************************
Using the Firebase Emulator Suite
*********************************

For an introduction to the Firebase Emulator suite, please visit the official documentation:
`https://firebase.google.com/docs/emulator-suite <https://firebase.google.com/docs/emulator-suite>`_

.. warning::
    Only the Auth and Realtime Database Emulators are currently supported in this PHP SDK.

To use the Firebase Emulator Suite, you must first `install it <https://firebase.google.com/docs/cli>`_.

See the `official documentation <https://firebase.google.com/docs/emulator-suite/install_and_configure#startup>`_
for instructions how to work with it.

The emulator suite must be running otherwise the PHP SDK can't connect to it.

Auth Emulator
-------------

If not already present, create a `firebase.json` file in the root of your project and make sure that at least the
following fields are set (the port number can be changed to your requirements):

.. code-block:: js

    {
      "emulators": {
        "auth": {
          "port": 9099
        }
      }
    }

Firebase Admin SDKs automatically connect to the Authentication emulator when the
``FIREBASE_AUTH_EMULATOR_HOST`` environment variable is set.

.. code-block:: bash

    $ export FIREBASE_AUTH_EMULATOR_HOST="localhost:9099"

With the environment variable set, Firebase Admin SDKs will accept unsigned ID Tokens and session cookies issued by the
Authentication emulator (via ``verifyIdToken`` and ``createSessionCookie`` methods respectively) to facilitate local
development and testing. Please make sure not to set the environment variable in production.

When connecting to the Authentication emulator, you will need to specify a project ID. You can pass a project ID to
the Factory directly or set the ``GOOGLE_CLOUD_PROJECT`` environment variable. Note that you do not need to use your
real Firebase project ID; the Authentication emulator will accept any project ID.

Realtime Database Emulator
--------------------------

If not already present, create a `firebase.json` file in the root of your project and make sure that at least the
following fields are set (the port number can be changed to your requirements):

.. code-block:: js

    {
      "emulators": {
        "database": {
          "port": 9100
        }
      }
    }

.. note::
    The Realtime Database Emulator uses port ``9000`` by default. This port is also used by PHP-FPM, so it is
    recommended to chose one that differs to not run into conflicts.

Firebase Admin SDKs automatically connect to the Realtime Database emulator when the
``FIREBASE_DATABASE_EMULATOR_HOST`` environment variable is set.

.. code-block:: bash

    $ export FIREBASE_DATABASE_EMULATOR_HOST="localhost:9100"
