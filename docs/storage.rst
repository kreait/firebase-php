#######
Storage
#######

Cloud Storage for Firebase stores your data in `Google Cloud Storage <https://cloud.google.com/storage>`_,
an exabyte scale object storage solution with high availability and global redundancy.

Before you start, please read about Firebase Cloud Storage in the official documentation:

- `Firebase Cloud Storage <https://firebase.google.com/docs/storage/>`_
- `Introduction to the Admin Cloud Storage API <https://firebase.google.com/docs/storage/admin/start>`_

You can work with your Firebase application's storage by invoking the ``getStorage()``
method of your Firebase instance:

.. code-block:: php

    use Kreait\Firebase;

    $firebase = (new Firebase\Factory())->create();
    $storage = $firebase->getStorage();

**********************
Default Storage bucket
**********************

The SDK assumes that your project's default storage bucket name has the format ``<project-id>.appspot.com``
and will configure the ``$firebase`` instance accordingly.

If you want to change the default bucket your instance works with, you can specify the name when using
the factory:

.. code-block:: php

    use Kreait\Firebase;

    $firebase = (new Firebase\Factory())
        ->withDefaultStorageBucket('another-default-bucket')
        ->create();

You can access the files on your storage in the following ways:

- via `Google Cloud Storage APIs <https://cloud.google.com/storage/docs/reference/libraries>`_
- via the Filesystem abstraction provided by the `PHP League <http://thephpleague.com>`_'s
  `league/flysystem <http://flysystem.thephpleague.com>`_ and
  `superbalist/flysystem-google-storage <https://github.com/Superbalist/flysystem-google-cloud-storage>`_

************************
Google Cloud Storage API
************************

Read about the usage of the Google Cloud Storage API in the
`official documentation <https://cloud.google.com/storage/docs/reference/libraries>`_.

.. code-block:: php

    // Get the default bucket
    $bucket = $storage->getBucket();

    // Get the bucket with the given name
    $bucket = $storage->getBucket('another-bucket');

**************************
The PHP League's Flysystem
**************************

Read about the usage of the PHP League's Flysystem at
`http://flysystem.thephpleague.com/api/ <http://flysystem.thephpleague.com/api/>`_.

.. code-block:: php

    // Get the default filesystem
    $filesystem = $storage->getFilesystem()

    // Get the bucket with the given name
    $filesystem = $storage->getFilesystem('another-bucket');
