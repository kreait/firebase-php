###############
Cloud Messaging
###############

You can use the Firebase Admin SDK for PHP to send Firebase Cloud Messaging messages to end-user devices. Specifically, you can send messages to individual devices, named topics, or condition statements that match one or more topics.

.. note::
    Sending messages to Device Groups is only possible with legacy protocols which are not supported
    by this SDK.

.. note::
    The Cloud Messaging API currently does not support sending messages tailored to different target platforms (Android, iOS and Web).

Before you start, please read about Firebase Remote Config in the official documentation:

- `Introduction to Firebase Cloud Messaging <https://firebase.google.com/docs/cloud-messaging/>`_
- `Introduction to Admin FCM API <https://firebase.google.com/docs/cloud-messaging/admin/>`_

***************
Getting started
***************

After having initialized your Firebase project instance, you can access the Cloud Messaging
component with ``$firebase->getMessaging()``.

.. code-block:: php

    use Kreait\Firebase;

    $firebase = (new Firebase\Factory())->create();
    $messaging = $firebase->getMessaging();

    $messaging->send($message);

A message must be an object implementing ``Kreait\Firebase\Messaging\Message`` or an array that can
be parsed to one of the supported message types.

The Supported message types are:

- A message to a given topic ``Kreait\Firebase\Messaging\MessageToTopic``
- A conditional message ``Kreait\Firebase\Messaging\ConditionalMessage``
- A message to a specific device ``Kreait\Firebase\Messaging\MessageToRegistrationToken``

A message can contain:

- A notification ``Kreait\Firebase\Messaging\Notification``
- Arbitrary data as an array of key-value pairs where all keys and values are strings

**********************
Creating Notifications
**********************

A notification is an instance of ``Kreait\Firebase\Messaging\Notification`` and can be
created in one of the following ways. The title and the body of a notification
are both optional.

.. code-block:: php

    use Kreait\Firebase\Messaging\Notification;

    $title = 'My Notification Title';
    $body = 'My Notification Body';

    $notification = Notification::fromArray([
        'title' => $title,
        'body' => $body
    ]);

    $notification = Notification::create($title, $body);

    $notification = Notification::create()
        ->withTitle($title)
        ->withBody($body);

Once you have created a message with one of the methods described below,
you can attach the notification to it:

.. code-block:: php

    $message = $message->withNotification($notification);

***************************
Attaching data to a message
***************************

The data attached to a message must be an array of key-value pairs
where all keys and values are strings.

Once you have created a message with one of the methods described below,
you can attach data to it:

.. code-block:: php

    $data = [
        'first_key' => 'First Value',
        'second_key' => 'Second Value',
    ];

    $message = $message->withData($data);


***********************
Send messages to topics
***********************

Based on the publish/subscribe model, FCM topic messaging allows you to send a message to multiple devices that have opted in to a particular topic. You compose topic messages as needed, and FCM handles routing and delivering the message reliably to the right devices.

For example, users of a local weather forecasting app could opt in to a "severe weather alerts" topic and receive notifications of storms threatening specified areas. Users of a sports app could subscribe to automatic updates in live game scores for their favorite teams.

Some things to keep in mind about topics:

- Topic messaging supports unlimited topics and subscriptions for each app.
- Topic messaging is best suited for content such as news, weather, or other publicly available information.
- Topic messages are optimized for throughput rather than latency. For fast, secure delivery to single devices or small groups of devices, target messages to registration tokens, not topics.

You can create a message to a topic in one of the following ways:

.. code-block:: php

    use Kreait\Firebase\Messaging\MessageToTopic;

    $topic = 'a-topic';

    $message = MessageToTopic::create($topic)
        ->withNotification($notification) // optional
        ->withData($data) // optional
    ;

    $message = MessageToTopic::fromArray([
        'topic' => $topic,
        'notification' => [/* Notification data as array */], // optional
        'data' => [/* data array */], // optional
    ]);

    $messaging->send($message);


*************************
Send conditional messages
*************************

Sometimes you want to send a message to a combination of topics. This is done by specifying a condition, which is a boolean expression that specifies the target topics. For example, the following condition will send messages to devices that are subscribed to ``TopicA`` and either ``TopicB`` or ``TopicC``:

``"'TopicA' in topics && ('TopicB' in topics || 'TopicC' in topics)"``

FCM first evaluates any conditions in parentheses, and then evaluates the expression from left to right. In the above expression, a user subscribed to any single topic does not receive the message. Likewise, a user who does not subscribe to TopicA does not receive the message. These combinations do receive it:

- ``TopicA`` and ``TopicB``
- ``TopicA`` and ``TopicC``

.. code-block:: php

    use Kreait\Firebase\Messaging\ConditionalMessage;

    $condition = "'TopicA' in topics && ('TopicB' in topics || 'TopicC' in topics)";

    $message = ConditionalMessage::create($condition)
        ->withNotification($notification) // optional
        ->withData($data) // optional
    ;

    $message = ConditionalMessage::fromArray([
        'condition' => $condition,
        'notification' => [/* Notification data as array */], // optional
        'data' => [/* data array */], // optional
    ]);

    $messaging->send($message);


*********************************
Send messages to specific devices
*********************************

The Admin FCM API allows you to send messages to individual devices by specifying a registration token for the target device. Registration tokens are strings generated by the client FCM SDKs for each end-user client app instance.

Each of the Firebase client SDKs are able to generate these registration tokens: `iOS <https://firebase.google.com/docs/cloud-messaging/ios/client#access_the_registration_token>`_, `Android <https://firebase.google.com/docs/cloud-messaging/android/client#sample-register>`_, `Web <https://firebase.google.com/docs/cloud-messaging/js/client#access_the_registration_token>`_, `C++ <https://firebase.google.com/docs/cloud-messaging/cpp/client#access_the_device_registration_token>`_, and `Unity <https://firebase.google.com/docs/cloud-messaging/unity/client#initialize_firebase_messaging>`_.

.. code-block:: php

    use Kreait\Firebase\Messaging\MessageToRegistrationToken;

    $deviceToken = '...';

    $message = MessageToRegistrationToken::create($deviceToken)
        ->withNotification($notification) // optional
        ->withData($data) // optional
    ;

    $message = MessageToRegistrationToken::fromArray([
        'token' => $deviceToken,
        'notification' => [/* Notification data as array */], // optional
        'data' => [/* data array */], // optional
    ]);

    $messaging->send($message);



