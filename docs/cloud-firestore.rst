###############
Cloud Firestore
###############

.. image:: https://img.shields.io/badge/available_since-v4.33-yellowgreen
   :target: https://github.com/kreait/firebase-php/releases/tag/4.33.0
   :alt: Available since v4.33

This SDK provides a bridge to the `google/cloud-firestore <https://packagist.org/packages/google/cloud-firestore>`_
package. You can enable the component in the SDK by adding the package to your project dependencies:

.. code-block:: bash

    composer require google/cloud-firestore

Alternatively, you can specify the package as a dependency in your project's existing composer.json file:

.. code-block:: js

    {
      "require": {
        "google/cloud-firestore": "^1.8",
        "kreait/firebase-php": "^4.33"
      }
   }


.. note::
    The ``google/cloud-firestore`` package requires the gRPC PHP extension to be installed. You can find installation
    instructions for gRPC at `github.com/grpc/grpc <https://github.com/grpc/grpc/tree/master/src/php>`_. The following
    projects aim to provide support for Firestore without the need to install the gRPC PHP extension, but have to
    be set up separately:

    - `ahsankhatri/firestore-php <https://github.com/ahsankhatri/firestore-php>`_
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

    use Kreait\Firebase\Firestore;

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
