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

You can work with your Firebase application's Remote Config by invoking the ``getRemoteConfig()``
method of your Firebase instance:

.. code-block:: php

    use Kreait\Firebase;

    $firebase = (new Firebase\Factory())->create();
    $remoteConfig = $firebase->getRemoteConfig();

*********************
Get the Remote Config
*********************

.. code-block:: php

    $template = $remoteConfig->get();
    echo json_encode($template, JSON_PRETTY_PRINT);

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
