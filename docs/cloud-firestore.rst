###############
Cloud Firestore
###############

This SDK provides a bridge to the `google/cloud-firestore <https://packagist.org/packages/google/cloud-firestore>`_
package. You can enable the component in the SDK by adding the package to your project dependencies:

.. code-block:: bash

    composer require google/cloud-firestore

.. note::
    The ``google/cloud-firestore`` package requires the gRPC PHP extension to be installed. You can find installation
    instructions for gRPC at `github.com/grpc/grpc <https://github.com/grpc/grpc/tree/master/src/php>`_. The following
    projects aim to provide support for Firestore without the need to install the gRPC PHP extension, but have to
    be set up separately:

    - `bensontrent/firestore-php <https://github.com/bensontrent/firestore-php>`_
    - `morrislaptop/firestore-php <https://github.com/morrislaptop/firestore-php>`_

Before you start, please read about Firestore in the official documentation:

- `Official Documentation <https://firebase.google.com/docs/firestore/>`_
- `google/cloud-firestore on GitHub <https://github.com/googleapis/google-cloud-php-firestore>`_
- `PHP API Documentation <https://googleapis.github.io/google-cloud-php/#/docs/cloud-firestore>`_
- `PHP Usage Examples <https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/firestore>`_

************************************
Initializing the Firestore component
************************************

**With the SDK**

.. code-block:: php

    $firestore = $factory->createFirestore();

**With Dependency Injection** (`Symfony Bundle <https://github.com/kreait/firebase-bundle>`_/`Laravel/Lumen Package <https://github.com/kreait/laravel-firebase>`_)

.. code-block:: php

    use Kreait\Firebase\Contract\Firestore;

    class MyService
    {
        public function __construct(Firestore $firestore)
        {
            $this->firestore = $firestore;
        }
    }

**With the Laravel** ``app()`` **helper** (`Laravel/Lumen Package <https://github.com/kreait/laravel-firebase>`_)

.. code-block:: php

    $firestore = app('firebase.firestore');

***************
Getting started
***************

.. code-block:: php

    $database = $firestore->database();

``$database`` is an instance of ``Google\Cloud\Firestore\FirestoreClient``. Please refer to the links above for
guidance on how to proceed from here.

******************************
Use another Firestore Database
******************************

If you don't specify a database, the Firestore Client will connect to the ``(default)`` database.

If you want to connect to another database, you can specify its name with the factory. You can work with multiple
Firestore Databases simultaneously.

.. code-block:: php

    use Kreait\Firebase\Factory;

    $factory = new Factory();

    $defaultDatabase = $factory
        ->createFirestore()
        ->database();

    $otherDatabase = $factory
        ->withFirestoreDatabase('another-database')
        ->createFirestore()
        ->database();

    $thirdDatabase = $factory
        ->withFirestoreDatabase('third-database')
        ->createFirestore()
        ->database();

***********************************
Add Firestore configuration options
***********************************

You can add additional configuration options for the Firestore Client used by the Firestore component:

.. code-block:: php

    use Kreait\Firebase\Factory;

    $factory = new Factory();

    $firestore = $factory
        ->withFirestoreClientConfig([...])
        ->createFirestore();

You can find all configuration options in the source code of the ``FirestoreClient`` class of the
`official Google Firestore PHP library <https://github.com/googleapis/google-cloud-php-firestore/blob/4186f2a2f2a8bdaedf19376a35ccb0ffad17f4e1/src/FirestoreClient.php#L138>`_.

In fact, the ``withFirestoreDatabase()`` method is a shortcut for the ``withFirestoreClientConfig()`` method:

.. code-block:: php

    use Kreait\Firebase\Factory;

    $factory = new Factory();

    $firestore = $factory->->withFirestoreDatabase('another-database');
    // is a shortcut for
    $firestore = $factory->withFirestoreClientConfig(['database' => 'another-database']);
