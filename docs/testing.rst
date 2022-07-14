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

To use the Firebase Emulator Suite, you must first `install it <https://firebase.google.com/docs/cli>`_.

.. warning::
    Only the Auth Emulator is currently supported. The other Emulators are not yet available,
    and side-effects could occur.

Auth Emulator
-------------

In your project, create a `firebase.json` file and make sure that at least the following fields are set
(the port number can be changed to your requirements):

.. code-block:: js

    {
      "emulators": {
        "auth": {
          "port": 9099
        }
      }
    }

The Firebase Admin SDK automatically connects to the Authentication emulator when the
``FIREBASE_AUTH_EMULATOR_HOST`` environment variable is set.

.. code-block:: bash

    $ export FIREBASE_AUTH_EMULATOR_HOST="localhost:9099"

With the environment variable set, Firebase Admin SDKs will accept unsigned ID Tokens and session cookies issued by the
Authentication emulator (via ``verifyIdToken`` and ``createSessionCookie`` methods respectively) to facilitate local
development and testing. Please make sure not to set the environment variable in production.

When connecting to the Authentication emulator, you will need to specify a project ID. You can pass a project ID to
the Factory directly or set the ``GOOGLE_CLOUD_PROJECT`` environment variable. Note that you do not need to use your
real Firebase project ID; the Authentication emulator will accept any project ID.
