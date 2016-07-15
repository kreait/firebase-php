###############
Retrieving data
###############

*************
Direct access
*************

.. code-block:: php

    $result = $firebase->get('my/data')

*****************
Using a Reference
*****************

.. code-block:: php

    $reference = $firebase->getReference('my/data');
    // or
    $reference = $firebase->my->data;

    $data = $reference->getData();

*******
Queries
*******

Queries can be executed on the Firebase object or a Reference.

.. code-block:: php

    use Kreait\Firebase\Firebase;
    use Kreait\Firebase\Query;

    $firebase = new Firebase(...);
    $query = new Query();

    $firebase->query($query);
    // or
    $firebase->my->data->query($query);

Query methods for sorting and filtering can be chained:

.. code-block:: php

    $query
        ->orderByKey()
        ->startAt('a')
        ->endAt('d');

Sorting results
===============

.. code-block:: php

    $query->orderByChildKey($key);
    $query->orderByKey();
    $query->orderByPriority();

Filtering results
=================

.. code-block:: php

    $query->limitToFirst($limit);
    $query->limitToLast($limit);
    $query->startAt($start);
    $query->endAt($end);
    $query->shallow();
