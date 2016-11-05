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

Shallow queries
===============

This is an advanced feature, designed to help you work with large datasets without needing to download
everything. Set this to true to limit the depth of the data returned at a location. If the data at
the location is a JSON primitive (string, number or boolean), its value will simply be returned.

If the data snapshot at the location is a JSON object, the values for each key will be
truncated to true.

Detailed information can be found on
`the official Firebase documentation page for shallow queries <https://firebase.google.com/docs/database/rest/retrieve-data#shallow>`_

.. code-block:: php

    $db->getReference('currencies')
        // order the reference's children by their key in ascending order
        ->shallow()
        ->getSnapshot();

A convenience method is available to retrieve the key names of a reference's children:

.. code-block:: php

    $db->getReference('currencies')->getChildKeys(); // returns an array of key names


Ordering data
=============

The official Firebase documentation explains
`How data is ordered <https://firebase.google.com/docs/database/rest/retrieve-data#section-rest-ordered-data>`_.

Data is always ordered in ascending order.

You can only order by one property at a time - if you try to order by multiple properties,
e.g. by child and by value, an exception will be thrown.

By key
------

.. code-block:: php

    $db->getReference('currencies')
        // order the reference's children by their key in ascending order
        ->orderByKey()
        ->getSnapshot();


By value
--------
.. note::
    In order to order by value, you must define an index, otherwise the Firebase API will
    refuse the query.

    .. code-block:: json

        {
            "currencies": {
                ".indexOn": ".value"
            }
        }

.. code-block:: php

    $db->getReference('currencies')
        // order the reference's children by their value in ascending order
        ->orderByValue()
        ->getSnapshot();


By child
--------
.. note::
    In order to order by a child value, you must define an index, otherwise the Firebase API will
    refuse the query.

    .. code-block:: json

        {
            "people": {
                ".indexOn": "height"
            }
        }

.. code-block:: php

    $db->getReference('people')
        // order the reference's children by the values in the field 'height' in ascending order
        ->orderByChild('height')
        ->getSnapshot();


Filtering data
==============

To be able to filter results, you must also define an order.

limitToFirst
------------

.. code-block:: php

    $db->getReference('people')
        // order the reference's children by the values in the field 'height'
        ->orderByChild('height')
        // limits the result to the first 10 children (in this case: the 10 shortest persons)
        // values for 'height')
        ->limitToFirst(10)
        ->getSnapshot();


limitToLast
-----------

.. code-block:: php

    $db->getReference('people')
        // order the reference's children by the values in the field 'height'
        ->orderByChild('height')
        // limits the result to the last 10 children (in this case: the 10 tallest persons)
        ->limitToLast(10)
        ->getSnapshot();

startAt
-------

.. code-block:: php

    $db->getReference('people')
        // order the reference's children by the values in the field 'height'
        ->orderByChild('height')
        // returns all persons taller than or exactly 1.68 (meters)
        ->startAt(1.68)
        ->getSnapshot();

endAt
-----

.. code-block:: php

    $db->getReference('people')
        // order the reference's children by the values in the field 'height'
        ->orderByChild('height')
        // returns all persons shorter than or exactly 1.98 (meters)
        ->endAt(1.98)
        ->getSnapshot();

equalTo
-------

.. code-block:: php

    $db->getReference('people')
        // order the reference's children by the values in the field 'height'
        ->orderByChild('height')
        // returns all persons being exactly 1.98 (meters) tall
        ->equalTo(1.98)
        ->getSnapshot();
