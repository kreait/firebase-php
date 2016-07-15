#################
Firebase PHP SDK
#################

A PHP client for the `Google Firebase <https://firebase.google.com>`_ Realtime Database

.. code-block:: php

    use Kreait\Firebase\Configuration;
    use Kreait\Firebase\Firebase;

    $config = new Configuration();
    $config->setAuthConfigFile('/path/to/google-service-account.json');

    $firebase = new Firebase('https://my-app.firebaseio.com', $config);

    $firebase->set(['key' => 'value'], 'my/data');

    print_r($firebase->get('my/data'));

    $firebase->delete('my/data');


**********
User Guide
**********

.. toctree::
   :maxdepth: 2

   overview
   authentication
   retrieving-data
   writing-data
   about
