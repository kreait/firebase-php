###############
Cloud Messaging
###############

.. image:: https://img.shields.io/badge/available_since-v4.5-yellowgreen
   :target: https://github.com/kreait/firebase-php/releases/tag/4.5.0
   :alt: Available since v4.5

You can use the Firebase Admin SDK for PHP to send Firebase Cloud Messaging messages to end-user devices. Specifically, you can send messages to individual devices, named topics, or condition statements that match one or more topics.

.. note::
    Sending messages to Device Groups is only possible with legacy protocols which are not supported
    by this SDK.

Before you start, please read about Firebase Remote Config in the official documentation:

- `Introduction to Firebase Cloud Messaging <https://firebase.google.com/docs/cloud-messaging/>`_
- `Introduction to Admin FCM API <https://firebase.google.com/docs/cloud-messaging/admin/>`_

***************
Getting started
***************

After having initialized the Firebase Factory, you can access the Cloud Messaging
component with ``$factory->createMessaging()``.

.. code-block:: php

    use Kreait\Firebase;
    use Kreait\Firebase\Messaging\CloudMessage;

    $messaging = (new Firebase\Factory())->createMessaging();

    $message = CloudMessage::withTarget(/* see sections below */)
        ->withNotification(Notification::create('Title', 'Body'))
        ->withData(['key' => 'value']);

    $messaging->send($message);

A message must be an object implementing ``Kreait\Firebase\Messaging\Message`` or an array that can
be parsed to a ``Kreait\Firebase\Messaging\CloudMessage``.

You can use ``Kreait\Firebase\Messaging\RawMessageFromArray`` to create a message without the SDK checking it
for validity before sending it. This gives you full control over the sent message, but also means that you
have to send/validate a message in order to know if it's valid or not.

.. note::
    If you notice that a field is not supported by the SDK yet, please open an issue on the issue tracker, so that others
    can benefit from it as well.

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

    use Kreait\Firebase\Messaging\CloudMessage;

    $topic = 'a-topic';

    $message = CloudMessage::withTarget('topic', $topic)
        ->withNotification($notification) // optional
        ->withData($data) // optional
    ;

    $message = CloudMessage::fromArray([
        'topic' => $topic,
        'notification' => [/* Notification data as array */], // optional
        'data' => [/* data array */], // optional
    ]);

    $messaging->send($message);


*************************
Send conditional messages
*************************

.. warning::
    OR-conditions are currently not processed correctly by the Firebase Rest API, leading to undelivered messages.
    This can be resolved by splitting up a message to an OR-condition into multiple messages to AND-conditions.
    So one conditional message to ``'a' in topics || 'b' in topics`` should be sent as two messages
    to the conditions ``'a' in topics && !('b' in topics)`` and ``'b' in topics && !('a' in topics)``

    References:
        - https://github.com/firebase/quickstart-js/issues/183
        - https://stackoverflow.com/a/52302136/284325

Sometimes you want to send a message to a combination of topics. This is done by specifying a condition, which is a boolean expression that specifies the target topics. For example, the following condition will send messages to devices that are subscribed to ``TopicA`` and either ``TopicB`` or ``TopicC``:

``"'TopicA' in topics && ('TopicB' in topics || 'TopicC' in topics)"``

FCM first evaluates any conditions in parentheses, and then evaluates the expression from left to right. In the above expression, a user subscribed to any single topic does not receive the message. Likewise, a user who does not subscribe to TopicA does not receive the message. These combinations do receive it:

- ``TopicA`` and ``TopicB``
- ``TopicA`` and ``TopicC``

.. code-block:: php

    use Kreait\Firebase\Messaging\CloudMessage;

    $condition = "'TopicA' in topics && ('TopicB' in topics || 'TopicC' in topics)";

    $message = CloudMessage::withTarget('condition', $condition)
        ->withNotification($notification) // optional
        ->withData($data) // optional
    ;

    $message = CloudMessage::fromArray([
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

    use Kreait\Firebase\Messaging\CloudMessage;

    $deviceToken = '...';

    $message = CloudMessage::withTarget('token', $deviceToken)
        ->withNotification($notification) // optional
        ->withData($data) // optional
    ;

    $message = CloudMessage::fromArray([
        'token' => $deviceToken,
        'notification' => [/* Notification data as array */], // optional
        'data' => [/* data array */], // optional
    ]);

    $messaging->send($message);

*********************************************
Send messages to multiple devices (Multicast)
*********************************************

.. image:: https://img.shields.io/badge/available_since-v4.24-yellowgreen
   :target: https://github.com/kreait/firebase-php/releases/tag/4.24.0
   :alt: Available since v4.24

You can send send one message to up to 500 devices:

.. code-block:: php

    use Kreait\Firebase\Messaging\CloudMessage;

    $deviceTokens = ['...', '...' /* ... */];

    $message = CloudMessage::new(); // Any instance of Kreait\Messaging\Message

    $sendReport = $messaging->sendMulticast($message, $deviceTokens);

The returned value is an instance of ``Kreait\Firebase\Messaging\MulticastSendReport`` and provides you with
methods to determine the successes and failures of the multicasted message:

.. code-block:: php

    $report = $messaging->sendMulticast($message, $deviceTokens);

    echo 'Successful sends: '.$report->successes()->count().PHP_EOL;
    echo 'Failed sends: '.$report->failures()->count().PHP_EOL;

    if ($report->hasFailures()) {
        foreach ($report->failures()->getItems() as $failure) {
            echo $failure->error()->getMessage().PHP_EOL;
        }
    }

******************************
Send multiple messages at once
******************************

.. image:: https://img.shields.io/badge/available_since-v4.29-yellowgreen
   :target: https://github.com/kreait/firebase-php/releases/tag/4.29.0
   :alt: Available since v4.29

You can send send up to 500 prepared messages (each message has a token, topic or condition as a target) in one go:

.. code-block:: php

    use ;

    $messages = [
        // Up to 500 items, either objects implementing Kreait\Firebase\Messaging\Message
        // or arrays that can be used to create valid to Kreait\Firebase\Messaging\Cloudmessage instances
    ];

    $message = CloudMessage::new(); // Any instance of Kreait\Messaging\Message

    /** @var Kreait\Firebase\Messaging\MulticastSendReport $sendReport **/
    $sendReport = $messaging->sendAll($messages);

*********************
Adding a notification
*********************

A notification is an instance of ``Kreait\Firebase\Messaging\Notification`` and can be
created in one of the following ways. The title and the body of a notification
are both optional.

.. code-block:: php

    use Kreait\Firebase\Messaging\Notification;

    $title = 'My Notification Title';
    $body = 'My Notification Body';
    $imageUrl = 'http://lorempixel.com/400/200/';

    $notification = Notification::fromArray([
        'title' => $title,
        'body' => $body,
        'image' => $imageUrl,
    ]);

    $notification = Notification::create($title, $body);

    $notification = Notification::create()
        ->withTitle($title)
        ->withBody($body)
        ->withImageUrl($imageUrl);

Once you have created a message with one of the methods described below,
you can attach the notification to it:

.. code-block:: php

    $message = $message->withNotification($notification);

***********
Adding data
***********

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

***************************
Changing the message target
***************************

You can change the target of an already created message with the ``withChangedTarget()`` method.

.. code-block:: php

    use Kreait\Firebase\Messaging\CloudMessage;

    $deviceToken = '...';
    $anotherDeviceToken = '...';

    $message = CloudMessage::withTarget('token', $deviceToken)
        ->withNotification(['title' => 'My title', 'body' => 'My Body'])
    ;

    $messaging->send($message);

    $sameMessageToDifferentTarget = $message->withChangedTarget('token', $anotherDeviceToken);


*********************************************
Adding target platform specific configuration
*********************************************

You can target platforms specific configuration to your messages.

Android
-------

You can find the full Android configuration reference in the official documentation:
`REST Resource: projects.messages.AndroidConfig <https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#androidconfig>`_

.. code-block:: php

    use Kreait\Firebase\Messaging\AndroidConfig;

    // Example from https://firebase.google.com/docs/cloud-messaging/admin/send-messages#android_specific_fields
    $config = AndroidConfig::fromArray([
        'ttl' => '3600s',
        'priority' => 'normal',
        'notification' => [
            'title' => '$GOOG up 1.43% on the day',
            'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
            'icon' => 'stock_ticker_update',
            'color' => '#f45342',
        ],
    ]);

    $message = $message->withAndroidConfig($config);

APNs
----

You can find the full APNs configuration reference in the official documentation:
`REST Resource: projects.messages.ApnsConfig <https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#apnsconfig>`_

.. code-block:: php

    use Kreait\Firebase\Messaging\ApnsConfig;

    // Example from https://firebase.google.com/docs/cloud-messaging/admin/send-messages#apns_specific_fields
    $config = ApnsConfig::fromArray([
        'headers' => [
            'apns-priority' => '10',
        ],
        'payload' => [
            'aps' => [
                'alert' => [
                    'title' => '$GOOG up 1.43% on the day',
                    'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                ],
                'badge' => 42,
            ],
        ],
    ]);

    $message = $message->withApnsConfig($config);


WebPush
-------

You can find the full WebPush configuration reference in the official documentation:
`REST Resource: projects.messages.Webpush <https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#webpushconfig>`_

.. code-block:: php

    use Kreait\Firebase\Messaging\WebPushConfig;

    // Example from https://firebase.google.com/docs/cloud-messaging/admin/send-messages#webpush_specific_fields
    $config = WebPushConfig::fromArray([
        'notification' => [
            'title' => '$GOOG up 1.43% on the day',
            'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
            'icon' => 'https://my-server/icon.png',
        ],
        'fcm_options' => [
            'link' => 'https://my-server/some-page',
        ],
    ]);

    $message = $message->withWebPushConfig($config);

***************************************
Adding platform independent FCM options
***************************************

.. image:: https://img.shields.io/badge/available_since-v4.27-yellowgreen
   :target: https://github.com/kreait/firebase-php/releases/tag/4.27.0
   :alt: Available since v4.27

You can find the full FCM Options configuration reference in the official documentation:
`REST Resource: projects.messages.fcm_options <https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#fcmoptions>`_

.. code-block:: php

    use Kreait\Firebase\Messaging\FcmOptions;

    $fcmOptions = FcmOptions::create()
        ->withAnalyticsLabel('my-analytics-label');
    // or
    $fcmOptions = [
        'analytics_label' => 'my-analytics-label';
    ];

    $message = $message->withFcmOptions($fcmOptions);

**************************************
Sending a fully configured raw message
**************************************

.. image:: https://img.shields.io/badge/available_since-v4.27-yellowgreen
   :target: https://github.com/kreait/firebase-php/releases/tag/4.27.0
   :alt: Available since v4.27

.. note::
    The message will be parsed and validated by the SDK.

.. code-block:: php

    use Kreait\Firebase\Messaging\RawMessageFromArray;

    $messaging = $factory->createMessaging();

    $message = new RawMessageFromArray([
            'notification' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#notification
                'title' => 'Notification title',
                'body' => 'Notification body',
                'image' => 'http://lorempixel.com/400/200/',
            ],
            'data' => [
                'key_1' => 'Value 1',
                'key_2' => 'Value 2',
            ],
            'android' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#androidconfig
                'ttl' => '3600s',
                'priority' => 'normal',
                'notification' => [
                    'title' => '$GOOG up 1.43% on the day',
                    'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                    'icon' => 'stock_ticker_update',
                    'color' => '#f45342',
                ],
            ],
            'apns' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#apnsconfig
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => '$GOOG up 1.43% on the day',
                            'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                        ],
                        'badge' => 42,
                    ],
                ],
            ],
            'webpush' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#webpushconfig
                'notification' => [
                    'title' => '$GOOG up 1.43% on the day',
                    'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                    'icon' => 'https://my-server/icon.png',
                ],
            ],
            'fcm_options' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#fcmoptions
                'analytics_label' => 'some-analytics-label'
            ]
        ]);

    $messaging->send($message);

*******************
Validating messages
*******************

.. image:: https://img.shields.io/badge/available_since-v4.12-yellowgreen
   :target: https://github.com/kreait/firebase-php/releases/tag/4.12.0
   :alt: Available since v4.12

You can validate a message by sending a validation-only request to the Firebase REST API. If the message is invalid,
a `Kreait\Firebase\Exception\Messaging\InvalidMessage` exception is thrown, which you can catch to evaluate the raw
error message(s) that the API returned.

.. code-block:: php

    use Kreait\Firebase\Exception\Messaging\InvalidMessage;

    $messaging = $factory->createMessaging();

    try {
        $messaging->validate($message);
    } catch (InvalidMessage $e) {
        print_r($e->errors());
    }


****************
Topic management
****************

.. image:: https://img.shields.io/badge/available_since-v4.8-yellowgreen
   :target: https://github.com/kreait/firebase-php/releases/tag/4.8.0
   :alt: Available since v4.8

Subscribe to a topic
--------------------

You can subscribe one or multiple devices to a topic by passing registration tokens to the
``subscribeToTopic()`` method.

.. code-block:: php

    $topic = 'my-topic';
    $registrationTokens = [
        // ...
    };

    $messaging = $factory->createMessaging();

    $messaging->subscribeToTopic($topic, $registrationTokens);

.. note::
    You can subscribe up to 1,000 devices in a single request. If you provide an array with over 1,000
    registration tokens, the operation will fail with an error.

Unsubscribe from a topic
------------------------

You can unsubscribe one or multiple devices from a topic by passing registration tokens to the
``unsubscribeFromTopic()`` method.

.. code-block:: php

    $topic = 'my-topic';
    $registrationTokens = [
        // ...
    };

    $messaging = $factory->createMessaging();

    $messaging->unsubscribeFromTopic($topic, $registrationTokens);

.. note::
    You can unsubscribe up to 1,000 devices in a single request. If you provide an array with over 1,000
    registration tokens, the operation will fail with an error.


***********************
App instance management
***********************

.. image:: https://img.shields.io/badge/available_since-v4.28-yellowgreen
   :target: https://github.com/kreait/firebase-php/releases/tag/4.28.0
   :alt: Available since v4.28

A registration token is related to an application that generated it. You can retrieve current information
about an app instance by passing a registration token to the ``getAppInstance()`` method.

.. code-block:: php

    $registrationToken = '...';

    $messagig = $factory->createMessaging();

    $appInstance = $messaging->getAppInstance($registrationToken);
    // Return the full information as provided by the Firebase API
    $instanceInfo = $appInstance->rawData();

    /* Example output for an Android application instance:
        [
          "applicationVersion" => "1060100"
          "connectDate" => "2019-07-21"
          "attestStatus" => "UNKNOWN"
          "application" => "com.vendor.application"
          "scope" => "*"
          "authorizedEntity" => "..."
          "rel" => array:1 [
            "topics" => array:3 [
              "test-topic" => array:1 [
                "addDate" => "2019-07-21"
              ]
              "test-topic-5d35b46a15094" => array:1 [
                "addDate" => "2019-07-22"
              ]
              "test-topic-5d35b46b66c31" => array:1 [
                "addDate" => "2019-07-22"
              ]
            ]
          ]
          "connectionType" => "WIFI"
          "appSigner" => "..."
          "platform" => "ANDROID"
        ]
    */

    /* Example output for a web application instance
        [
          "application" => "webpush"
          "scope" => ""
          "authorizedEntity" => "..."
          "rel" => array:1 [
            "topics" => array:2 [
              "test-topic-5d35b445b830a" => array:1 [
                "addDate" => "2019-07-22"
              ]
              "test-topic-5d35b446c0839" => array:1 [
                "addDate" => "2019-07-22"
              ]
            ]
          ]
          "platform" => "BROWSER"
        ]
    */

.. note::
    As the data returned by the Google Instance ID API can return differently formed results depending on the
    application or platform, it is currently difficult to add reliable convenience methods for specific
    fields in the raw data.

Working with topic subscriptions
--------------------------------

You can retrieve all topic subscriptions for an app instance with the ``topicSubscriptions()`` method:

.. code-block:: php

    $messaging = $factory->createMessaging();
    $appInstance = $messaging->getAppInstance('<registration token>');

    /** @var \Kreait\Firebase\Messaging\TopicSubscriptions $subscriptions */
    $subscriptions = $appInstance->topicSubscriptions();

    foreach ($subscriptions as $subscription) {
        echo "{$subscription->registrationToken()} is subscribed to {$subscription->topic()}\n";
    }
