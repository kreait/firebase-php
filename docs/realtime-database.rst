#################
Realtime Database
#################

.. note::
    The Realtime Database API currently does not support realtime event listeners.

You can work with your Firebase application's Realtime Database by invoking the ``getDatabase()``
method of your Firebase instance:

.. code-block:: php

    use Kreait\Firebase;

    $firebase = (new Firebase\Factory())->create();
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

***********
Saving data
***********

Set/replace values
==================

For basic write operations, you can use set() to save data to a specified reference,
replacing any existing data at that path. For example a configuration array for
a website might be set as follows:

.. code-block:: php

    $db->getReference('config/website')
       ->set([
           'name' => 'My Application',
           'emails' => [
               'support' => 'support@domain.tld',
               'sales' => 'sales@domain.tld',
           ],
           'website' => 'https://app.domain.tld',
          ]);

    $db->getReference('config/website/name')->set('New name');

.. note::
    Using ``set()`` overwrites data at the specified location, including any child nodes.

Update specific fields [#f1]_
=============================

To simultaneously write to specific children of a node without overwriting other child nodes,
use the update() method.

When calling ``update()``, you can update lower-level child values by specifying a path for
the key. If data is stored in multiple locations to scale better, you can update all
instances of that data using data fan-out.

For example, in a blogging app you might want to add a post and simultaneously update it
to the recent activity feed and the posting user's activity feed using code like this:

.. code-block:: php

    $uid = 'some-user-id';
    $postData = [
        'title' => 'My awesome post title',
        'body' => 'This text should be longer',
    ];

    // Create a key for a new post
    $newPostKey = $db->getReference('posts')->push()->getKey();

    $updates = [
        'posts/'.$newPostKey => $postData,
        'user-posts/'.$uid.'/'.$newPostKey => $postData,
    ];

    $db->getReference() // this is the root reference
       ->update($updates);


Writing lists [#f2]_
====================

Use the ``push()`` method to append data to a list in multiuser applications. The ``push()`` method
generates a unique key every time a new child is added to the specified Firebase reference.
By using these auto-generated keys for each new element in the list, several clients can
add children to the same location at the same time without write conflicts.
The unique key generated by ``push()`` is based on a timestamp, so list
items are automatically ordered chronologically.

You can use the reference to the new data returned by the ``push()`` method to get the value of the
child's auto-generated key or set data for the child. The ``getKey()`` method of a
``push()`` reference contains the auto-generated key.

.. code-block:: php

    $postData = [...];
    $postRef = $db->getReference('posts')->push($postData);

    $postKey = $postRef->getKey(); // The key looks like this: -KVquJHezVLf-lSye6Qg

Server values
=============

Server values can be written at a location using a placeholder value which is an object with a single
``.sv`` key. The value for that key is the type of server value you wish to set.

Firebase currently supports only one server value: ``timestamp``. You can either set it
manually in your write operation, or use a constant from the ``Firebase\Database`` class.

The following to usages are equivalent:

.. code-block:: php

    $ref = $db->getReference('posts/my-post')
              ->set('created_at', ['.sv' => 'timestamp']);

    $ref = $db->getReference('posts/my-post')
              ->set('created_at', Database::SERVER_TIMESTAMP);


Delete data [#f3]_
==================

You can delete a reference, including all data it contains, with the ``remove()`` method:

.. code-block:: php

    $db->getReference('posts')->remove();

You can also delete by specifying null as the value for another write operation such as
``set()`` or ``update()``.

.. code-block:: php

    $db->getReference('posts')->set(null);

You can use this technique with ``update()`` to delete multiple children in a single API call.

*********************
Database transactions
*********************

.. note::
    Support for database transactions has been added in release 4.21.0

You can use transaction to update data according to its existing state. For example, if you want to increase
an upvote counter, and want to make sure the count accurately reflects multiple, simultaneous upvotes,
use a transaction to write the new value to the counter. Instead of two writes that change the
counter to the same number, one of the write requests fails and you can then retry the
request with the new value.

Replace data inside a transaction
=================================

.. code-block:: php

    use Kreait\Firebase\Database\Transaction;

    $counterRef = $db->getReference('counter');

    $db->runTransaction(function (Transaction $transaction) use ($counterRef) {

        // You have to snapshot the reference in order to change its value
        $counterSnapshot = $transaction->snapshot($counterRef);

        // Get the existing value from the snapshot
        $counter = $counterSnapshot->getValue() ?: 0;
        $newCounter = ++$counter;

        // If the value hasn't changed in the Realtime Database while we are
        // incrementing it, the transaction will be a success.
        $transaction->set($counterRef, $newCounter);
    });

Delete data inside a transaction
================================

Likewise, you can wrap the removal of a reference in a transaction as well: you can remove the reference
only if it hasn't changed in the meantime.

.. code-block:: php

    use Kreait\Firebase\Database\Transaction;

    $toBeDeleted = $db->getReference('to-be-deleted');

    $db->runTransaction(function (Transaction $transaction) use ($toBeDeleted) {

        $transaction->snapshot($toBeDeleted);

        $transaction->remove($toBeDeleted);
    });

Handling transaction failures
=============================

If you haven't snapshotted a reference before trying to change it, the operation will fail
with a ``\Kreait\Firebase\Exception\Database\ReferenceHasNotBeenSnapshotted`` error.

If the reference has changed in the Realtime Database after you started the transaction,
the transaction will fail with a ``\Kreait\Firebase\Exception\Database\TransactionFailed``
error.

.. code-block:: php

    use Kreait\Firebase\Database\Transaction;
    use Kreait\Firebase\Exception\Database\ReferenceHasNotBeenSnapshotted;
    use Kreait\Firebase\Exception\Database\TransactionFailed;

    $ref = $db->getReference('my-ref');

    try {
        $db->runTransaction(function (Transaction $transaction) use ($ref) {

            // $transaction->snapshot($ref);

            $ref->set('value change without a transaction');

            $transaction->set($ref, 'this will fail');
        });

    } catch (ReferenceHasNotBeenSnapshotted $e) {

        $referenceInQuestion = $e->getReference();

        echo $e->getReference()->getUri().': '.$e->getMessage();

    } catch (TransactionFailed $e) {

        $referenceInQuestion = $e->getReference();
        $failedRequest = $e->getRequest();
        $failureResponse = $e->getResponse();

        echo $e->getReference()->getUri().': '.$e->getMessage();

    }


************************
Debugging API exceptions
************************

When a request to Firebase fails, the SDK will throw a ``\Kreait\Firebase\Exception\ApiException`` that
includes the sent request and the received response object:

.. code-block:: php

    try {
        $db->getReference('forbidden')->getValue();
    } catch (ApiException $e) {
        /** @var \Psr\Http\Message\RequestInterface $request */
        $request = $e->getRequest();
        /** @var \Psr\Http\Message\ResponseInterface|null $response */
        $response = $e->getResponse();

        echo $request->getUri().PHP_EOL;
        echo $request->getBody().PHP_EOL;

        if ($response) {
            echo $response->getBody();
        }
    }


**************
Database rules
**************

Learn more about the usage of Firebase Realtime Database Rules in the
`official documentation <https://firebase.google.com/docs/database/security/>`_.

.. code-block:: php

        use Kreait\Firebase\Database\RuleSet;

        // The default rules allow full read and write access to authenticated users of your app
        $ruleSet = RuleSet::default();

        // This level of access means anyone can read or write to your database. You should
        // configure more secure rules before launching your app.
        $ruleSet = RuleSet::public();

        // Private rules disable read and write access to your database by users.
        // With these rules, you can only access the database through the
        // Firebase console and the Admin SDKs.
        $ruleSet = RuleSet::private();

        // You can define custom rules
        $ruleSet = RuleSet::fromArray(['rules' => [
            '.read' => true,
            '.write' => false,
            'users' => [
                '$uid' => [
                    '.read' => '$uid === auth.uid',
                    '.write' => '$uid === auth.uid',
                ]
            ]
        ]]);

        $db->updateRules($ruleSet);

        $freshRuleSet = $db->getRuleSet(); // Returns a new RuleSet instance
        $actualRules = $ruleSet->getRules(); // returns an array


.. rubric:: Footnotes

.. [#f1] This example and its description is the same as in the official documentation:
         `Update specific fields <https://firebase.google.com/docs/database/web/read-and-write#update_specific_fields>`_.
.. [#f2] This example and its description is the same as in the official documentation:
         `Append to a list of data <https://firebase.google.com/docs/database/web/lists-of-data#append_to_a_list_of_data>`_.
.. [#f3] This example and its description is the same as in the official documentation:
         `Delete data <https://firebase.google.com/docs/database/web/read-and-write#delete_data>`_.
