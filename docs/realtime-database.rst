#################
Realtime Database
#################

.. code-block:: php

    use Kreait\Firebase;

    $firebase = Firebase::create(__DIR__.'/google-service-account.json');
    $database = $firebase->getDatabase();

***************
CRUD Operations
***************

set
===

Writes or replace a value to the given location

.. code-block:: php

    $database->set('path/to/location', true);
    $database->set('path/to/location', false);
    $database->set('path/to/location', 123);
    $database->set('path/to/location', 'a string');
    $database->set('path/to/location', ['foo' => 'value', 'bar' => 'value']);
