##########
Quickstart
##########

.. code-block:: php

    use Kreait\Firebase;

    $firebase = Firebase::create(__DIR__.'/google-service-account.json');

.. _quickstart-database:

***************************
Using the Realtime Database
***************************

.. code-block:: php

    $db = $firebase->getDatabase();

    try {
        $db->set('blog', ['title' => 'My blog']);

        $id = $db->push('blog/posts', ['title' => 'My first post']);
        echo $id;
        // => "-<generated id>"

        $db->update('blog/posts/'.$id, ['tags' => ['first', 'post']]);

        echo $db->get('blog');
        // => "{"blog": {...}}"

        $db->delete('blog');
    } catch (Firebase\Exception\Database $e) {
        echo $e->getMessage();
    }

.. _quickstart-storage:

*****************
Using the Storage
*****************

.. code-block:: php

    $storage = $firebase->getStorage();

    try {
        $storage->put('text_files/test.txt', 'abc');

        echo $storage->read('text_files/test.txt');
        // => "abc"
    } catch (\Throwable $e) {
        echo $e->getMessage();
    }

