.. title:: Firebase PHP

#################
Firebase PHP SDK
#################

The Firebase PHP SDK makes it easy to work with Google
Firebase Realtime Databases and Storages.

.. code-block:: php

    use Kreait\Firebase;

    $firebase = Firebase::create(__DIR__.'/google-service-account.json');

    $db = $firebase->getDatabase();

    try {
        $db->set('blog', ['title' => 'My blog']);
        $id = $db->push('blog/posts', ['title' => 'My first post']);
        $db->update('blog/posts/'.$id, ['tags' => ['first', 'post']]);

        echo $db->get('blog');

        $db->delete('blog');
    } catch (Firebase\Exception\Database $e) {
        echo $e->getMessage();
    }

**********
User Guide
**********

.. toctree::
   :maxdepth: 2

   overview
   quickstart
   realtime-database
   storage
