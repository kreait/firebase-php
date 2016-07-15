##############
Authentication
##############

*****************************
With a Google Service Account
*****************************

To use this authentication method, you must first create a Service account
as described in the
`official documentation <https://firebase.google.com/docs/server/setup#add_firebase_to_your_app>`_.

.. note::
    The Service Account needs at least the *Editor* role to be able to modify
    data.

Put the downloaded credentials JSON file in a path of your project and
configure the Firebase SDK to use it:

.. code-block:: php

    use Kreait\Firebase\Configuration;
    use Kreait\Firebase\Firebase;

    $config = new Configuration();
    $config->setAuthConfigFile(__DIR__.'google-service-account.json');

    $firebase = new Firebase('https://my-app.firebaseio.com', $config);

All requests to the Firebase Realtime Database will now be performed with
the permissions of this Service Account.

**********************
With a database secret
**********************

.. warning::
    Using the database secret is considered a legacy method. It is not sure
    how long Google will support this authentication method.

After retrieving a database secret from the project settings of your Firebase
application, you can configure the Firebase SDK with it:

.. code-block:: php

    use Kreait\Firebase\Configuration;
    use Kreait\Firebase\Firebase;

    $config = new Configuration();
    $config->setFirebaseSecret('...');

    $firebase = new Firebase('https://my-app.firebaseio.com', $config);

All requests to the Firebase Realtime Database will now be performed with
full read and write access.

************************
Authentication overrides
************************

You can override the main authentication to impersonate users of your
application by providing a UID and optionally some claims to the Firebase
object:

.. code-block:: php

    $uid = 'some-unique-user-id';
    $claims = ['premiumUser' => true];

    $firebase->setAuthOverride($uid, $claims);

    $data = $firebase->get('/premium-content');

    $firebase->removeAuthOverride();
