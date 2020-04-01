#############
Remote Config
#############

Change the behavior and appearance of your app without publishing an app update.

Firebase Remote Config is a cloud service that lets you change the behavior and appearance of your app without
requiring users to download an app update. When using Remote Config, you create in-app default values that
control the behavior and appearance of your app.

Before you start, please read about Firebase Remote Config in the official documentation:

- `Firebase Remote Config <https://firebase.google.com/docs/remote-config/>`_

****************
Before you begin
****************

For Firebase projects created before the March 7, 2018 release of the Remote Config REST API, you must enable the API in the Google APIs console.

1. Open the `Firebase Remote Config API page <https://console.developers.google.com/apis/api/firebaseremoteconfig.googleapis.com/overview?project=_>`_ in the Google APIs console.
2. When prompted, select your Firebase project. (Every Firebase project has a corresponding project in the Google APIs console.)
3. Click Enable on the Firebase Remote Config API page.

********************************************
Initializing the Realtime Database component
********************************************

**With the SDK**

.. code-block:: php

    $remoteConfig = $factory->createRemoteConfig();

**With Dependency Injection** (`Symfony Bundle <https://github.com/kreait/firebase-bundle>`_/`Laravel/Lumen Package <https://github.com/kreait/laravel-firebase>`_)

.. code-block:: php

    use Kreait\Firebase\RemoteConfig;

    class MyService
    {
        public function __construct(Database $remoteConfig)
        {
            $this->remoteConfig = $remoteConfig;
        }
    }

**With the Laravel** ``app()`` **helper** (`Laravel/Lumen Package <https://github.com/kreait/laravel-firebase>`_)

.. code-block:: php

    $remoteConfig = app('firebase.remote_config');

*********************
Get the Remote Config
*********************

.. code-block:: php

    $template = $remoteConfig->get(); // Returns a Kreait\Firebase\RemoteConfig\Template

    // Added in 4.29.0
    $version = $template->version(); // Returns a Kreait\Firebase\RemoteConfig\Version

**************************
Create a new Remote Config
**************************

.. code-block:: php

    use Kreait\Firebase\RemoteConfig;

    $template = RemoteConfig\Template::new();

***************
Add a condition
***************

.. code-block:: php

    use Kreait\Firebase\RemoteConfig;

    $germanLanguageCondition = RemoteConfig\Condition::named('lang_german')
        ->withExpression("device.language in ['de', 'de_AT', 'de_CH']")
        ->withTagColor(TagColor::ORANGE); // The TagColor is optional

    $template = $template->withCondition($germanLanguageCondition);

***************
Add a parameter
***************

.. code-block:: php

    use Kreait\Firebase\RemoteConfig;

    $welcomeMessageParameter = Parameter::named('welcome_message')
            ->withDefaultValue('Welcome!')
            ->withDescription('This is a welcome message') // optional
    ;

******************
Conditional values
******************

.. code-block:: php

    use Kreait\Firebase\RemoteConfig;

    $germanLanguageCondition = RemoteConfig\Condition::named('lang_german')
        ->withExpression("device.language in ['de', 'de_AT', 'de_CH']");

    $germanWelcomeMessage = RemoteConfig\ConditionalValue::basedOn($germanLanguageCondition, 'Willkommen!');

    $welcomeMessageParameter = Parameter::named('welcome_message')
            ->withDefaultValue('Welcome!')
            ->withConditionalValue($germanWelcomeMessage);

    $template = $template
        ->withCondition($germanLanguageCondition)
        ->withParameter($welcomeMessageParameter);

.. note::
    When you use a conditional value, make sure to add the corresponding condition to the template first.

**********
Validation
**********

Usually, the SDK will protect you from creating an invalid Remote Config template in the first
place. If you want to be sure, you can validate the template with a call to the Firebase API:

.. code-block:: php

    use Kreait\Firebase\Exception\RemoteConfig\ValidationFailed;

    try {
        $remoteConfig->validate($template);
    } catch (ValidationFailed $e) {
        echo $e->getMessage();
    }

.. note::
    The ``ValidationFailed`` exception extends ``Kreait\Firebase\Exception\RemoteConfigException``,
    so you can safely use the more generic exception type as well.

*************************
Publish the Remote Config
*************************

.. code-block:: php

    use Kreait\Firebase\Exception\RemoteConfigException

    try {
        $remoteConfig->publish($template);
    } catch (RemoteConfigException $e) {
        echo $e->getMessage();
    }

*********************
Remote Config history
*********************

Since August 23, 2018, Firebase provides a change history for your published Remote configs.

The following properties are available from a ``Kreait\Firebase\RemoteConfig\Version`` object:

.. code-block:: php

    $version->versionNumber();
    $version->user(); // The user/service account the performed the change
    $version->description();
    $version->updatedAt();
    $version->updateOrigin();
    $version->updateType();
    $version->rollBackSource();


List versions
-------------

To enhance performance and prevent memory issues when retrieving a huge amount of versions,
this methods returns a `Generator <http://php.net/manual/en/language.generators.overview.php>`_.

.. code-block:: php

    foreach ($auth->listVersions() as $version) {
        /** @var \Kreait\Firebase\RemoteConfig\Version $version */
        // ...
    }

    // or

    array_map(function (\Kreait\Firebase\RemoteConfig\Version $version) {
        // ...
    }, iterator_to_array($auth->listVersions()));

Filtering
---------

You can filter the results of ``RemoteConfig::listVersions()``:

.. code-block:: php

    use Kreait\Firebase\RemoteConfig\FindVersions;

    $query = FindVersions::all()
        // Versions created/updated after August 1st, 2019 at midnight
        ->startingAt(new DateTime('2019-08-01 00:00:00'))
        // Versions created/updated before August 7th, 2019 at the end of the day
        ->endingAt(new DateTime('2019-08-06 23:59:59'))
        // Versions with version numbers smaller than 3464
        ->upToVersion(VersionNumber::fromValue(3463))
        // Setting a page size can results in faster first results,
        // but results in more request
        ->withPageSize(5)
        // Stop querying after the first 10 results
        ->withLimit(10)
    ;

    // Alternative array notation

    $query = [
        'startingAt' => '2019-08-01',
        'endingAt' => '2019-08-07',
        'upToVersion' => 9999,
        'pageSize' => 5,
        'limit' => 10,
    ];

    foreach ($remoteConfig->listVersions($query) as $version) {
        echo "Version number: {$version->versionNumber()}\n";
        echo "Last updated at {$version->updatedAt()->format('Y-m-d H:i:s')}\n";
        // ...
        echo "\n---\n";
    }

Get a specific version
----------------------

.. code-block:: php

    $version = $remoteConfig->getVersion($versionNumber);


Rollback to a version
---------------------

.. code-block:: php

    $template = $remoteConfig->rollbackToVersion($versionNumber);
