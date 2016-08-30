#################
Realtime Database
#################

You can work with your Firebase application's Realtime Database by invoking the ``getDatabase()``
method of your Firebase instance:

.. code-block:: php

    $firebase = Firebase::fromServiceAccount(...);
    $database = $firebase->getDatabase();


***************
Retrieving data
***************

Every node in your database can be accessed through a Reference:

.. code-block:: php

    $reference = $database->getReference('path/to/child/location');

.. note::
    Creating a reference does not result in a request to your Database. Requests to your Firebase
    applications are executed with the ``getSnapshot()`` and ``getValue()`` methods only.

You can then retrieve a Database Snapshot for the Reference or its value directly:

.. code-block:: php

    $snapshot = $reference->getSnapshot();

    $value = $snapshot->getValue();
    // or
    $value = $reference->getValue();


Database Snapshots
==================

Database Snapshots are immutable copies of the data at a Firebase Database location at the time of a
query. The can't be modified and will never change.

.. code-block:: php

    $snapshot = $reference->getSnapshot();
    $value = $snapshot->getValue();

    $value = $reference->getValue(); // Shortcut for $reference->getSnapshot()->getValue();

Snapshots provide additional methods to work with and analyze the contained value:

- ``exists()`` returns true if the Snapshot contains any (non-null) data.
- ``getChild()`` returns another Snapshot for the location at the specified relative path.
- ``getKey()`` returns the key (last part of the path) of the location of the Snapshot.
- ``getReference()`` returns the Reference for the location that generated this Snapshot.
- ``getValue()`` returns the data contained in this Snapshot.
- ``hasChild()`` returns true if the specified child path has (non-null) data.
- ``hasChildren()`` returns true if the Snapshot has any child properties, i.e. if the value is an array.
- ``numChildren()`` returns the number of child properties of this Snapshot, if there are any.

Queries
=======

You can use Queries to filter and order the results returned from the Realtime Database. Queries behave exactly
like References. That means you can execute any method on a Query that you can execute on a Reference.

.. note::
    You can combine every filter query with every order query, but not multiple queries of each type.
    Shallow queries are a special case: they can not be combined with any other query method.

.. note::
    Your Realtime Database must be indexed to be querieable, except for node keys, which are always indexed.
    You can learn how to index your data in the official documentation:
    `Index your data <https://firebase.google.com/docs/database/security/indexing-data>`_.

    An upcoming release of the PHP SDK will be able to index your data on demand, as soon as you perform a query.

Ordering data
=============

By child
--------

By child value
--------------

By key
------

Filtering data
==============

By a specified child key
------------------------

By key
------

By value
--------

