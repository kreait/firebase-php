#############
Cloud Storage
#############

Cloud Storage for Firebase stores your data in `Google Cloud Storage <https://cloud.google.com/storage>`_,
an exabyte scale object storage solution with high availability and global redundancy.

This SDK provides a bridge to the `google/cloud-storage <https://packagist.org/packages/google/cloud-storage>`_
package. You can enable the component in the SDK by adding the package to your project dependencies:

Before you start, please read about Firebase Cloud Storage in the official documentation:

- `Firebase Cloud Storage <https://firebase.google.com/docs/storage/>`_
- `Introduction to the Admin Cloud Storage API <https://firebase.google.com/docs/storage/admin/start>`_
- `PHP API Documentation <https://googleapis.github.io/google-cloud-php/#/docs/cloud-storage>`_
- `PHP Usage examples <https://github.com/GoogleCloudPlatform/php-docs-samples/blob/master/storage>`_

**********************************
Initializing the Storage component
**********************************

**With the SDK**

.. code-block:: php

    $storage = $factory->createStorage();

**With Dependency Injection** (`Symfony Bundle <https://github.com/kreait/firebase-bundle>`_/`Laravel/Lumen Package <https://github.com/kreait/laravel-firebase>`_)

.. code-block:: php

    use Kreait\Firebase\Contract\Storage;

    class MyService
    {
        public function __construct(Storage $storage)
        {
            $this->storage = $storage;
        }
    }

**With the Laravel** ``app()`` **helper** (`Laravel/Lumen Package <https://github.com/kreait/laravel-firebase>`_)

.. code-block:: php

    $storage = app('firebase.storage');


***************
Getting started
***************

.. code-block:: php

    $storageClient = $storage->getStorageClient();
    $defaultBucket = $storage->getBucket();
    $anotherBucket = $storage->getBucket('another-bucket');

**********************
Default Storage bucket
**********************

.. note::
    It is not necessary to change the default storage bucket in most cases.

The SDK assumes that your project's default storage bucket name has the format ``<project-id>.appspot.com``
and will configure the storage instance accordingly.

If you want to change the default bucket your instance works with, you can specify the name when using
the factory:

.. code-block:: php

    use Kreait\Firebase\Factory;

    $storage = (new Factory())
        ->withDefaultStorageBucket('another-default-bucket')
        ->createStorage();
