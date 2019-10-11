# CHANGELOG

## 4.33.0 - 2019-10-11

### Added

#### Firestore

* You can now access your project's Firestore database. ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-firestore.html))

#### General

* It is now possible to override the HTTP Handler used for API requests. ([Documentation](https://firebase-php.readthedocs.io/en/latest/setup.html#using-a-custom-http-handler))

### Changes

#### Messaging

* When sending a message with a notification that has neither a title nor a body, the Firebase API returns an error.
  This is now prevented by checking for the existence of one of both when creating a notification. It is still
  possible to explicitely use empty strings.

#### Storage

* The direct integration of [`league/flystem`](https://github.com/thephpleague/flysystem) via
  [`superbalist/flysystem-google-storage`](https://github.com/Superbalist/flysystem-google-cloud-storage) and
  by `\Kreait\Firebase\Storage::getFilesystem()` has been deprecated.

#### General

* Using the `Kreait\Firebase` class has been deprecated. Please instantiate the services you need directly: 

```php
<?php

use Kreait\Firebase;

# deprecated
$firebase = (new Firebase\Factory())
    // ->withServiceAccount(...)
    // ->...
    ->create()
;

$auth = $firebase->getAuth();
$database = $firebase->getDatabase();
$messaging = $firebase->getMessaging();
$remoteConfig = $firebase->getRemoteConfig();
$storage = $firebase->getStorage();

# recommended
$factory = (new Firebase\Factory())
    // ->withServiceAccount(...)
    // ->...
    // no call to ->create()
;

$auth = $factory->createAuth();
$database = $factory->createDatabase();
$messaging = $factory->createMessaging();
$remoteConfig = $factory->createRemoteConfig();
$storage = $factory->createStorage();
```

* When using `Kreait\Firebase\Factory::withServiceAccount()` auto-discovery will be disabled.
* Calling a deprecated method will trigger a `E_USER_DEPRECATED` warning (only if PHP is configured to show them). 

## 4.32.0 - 2019-09-13

### Added

#### Dynamic Links

* You can now create Dynamic Links and retrieve statistics for Dynamic Links. ([Documentation](https://firebase-php.readthedocs.io/en/latest/dynamic-links.html))
  
  ```php
  use Kreait\Firebase;

  $firebaseFactory = new Firebase\Factory();

  $dynamicLinksDomain = 'https://example.page.link';
  $dynamicLinks = $firebaseFactory->createDynamicLinksService($dynamicLinksDomain);
  
  $shortLink = $dynamicLinks->createShortLink('https://domain.tld/some/path');
  $stats = $dynamicLinks->getStatistics('https://example.page.link/wXYZ');
  ```

### Changes

#### General

* It is now possible to give the path to a Service Account JSON file directly to the factory instead of instantiating a
  `ServiceAccount` instance beforehand.
  
  ```php
  use Kreait\Firebase;
  
  $firebase = (new Firebase\Factory())
      ->withServiceAccount('/path/to/google-service-account.json')
      ->create();
  ```

#### Realtime Database

- `Kreait\Firebase\Database::getRules()` has been deprecated in favor of `Kreait\Firebase\Database::getRuleSet()`

## 4.31.0 - 2019-08-22

### Bugfixes

#### Messaging

* Fixed the inability to correctly parse a response from the Firebase Batch Messaging when `Messaging::sendMulticast()`
  or `Messaging::sendAll()` was used with only one recipient. 

### Changes

#### Auth

* The third parameter of `Kreait\Firebase\Auth::verifyIdToken()` (`$allowTimeInconsistencies`) has been deprecated
  because, since 4.25.0, a default leeway of 5 minutes is already applied. Using it will trigger a `E_USER_DEPRECATED`
  warning.
* Previously the "verified" status of an user email could be `null` if not defined - it will now be `false` by default 

## 4.30.1 - 2019-08-17

### Changes

#### Database

* Fixed a deprecation warning when getting the root reference with `Kreait\Firebase\Database::getReference()` 
  without giving a path.
  
#### Messaging

* When sending multiple messages at once, it can happen in some cases that the HTTP sub-responses can not be parsed
  which would cause an exception. Until we figure out the cause, those exceptions are now caught, with the caveat
  that the resulting send-report is not correct (it will show 0 successes and 0 failures even if the messages
  were successfully sent).

## 4.30.0 - 2019-08-16

### Changes

``Kreait\Firebase\Factory`` now exposes the methods to create the single components (Auth, Messaging, Remote Config,
Storage) directly in order to enable its usage in [kreait/laravel-firebase](https://github.com/kreait/laravel-firebase).

## 4.29.0 - 2019-08-13

### Added

#### Cloud Messaging

* Added `Kreait\Firebase\Messaging::sendAll()` to send up to 100 messages to multiple targets (tokens, topics, and
  conditions) in one request.
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#send-multiple-messages-at-once))
* A condition will now ensure that no more than five topics are provided. Previously, the Firebase REST API would 
  have rejected the message with a non-specific "Invalid condition expression provided."
  
#### Remote Config

* The Remote Config history can now be filtered. 
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/remote-config.html#filtering))
* A published Remote Config template now contains a version that can be retrieved with 
  `Kreait\Firebase\RemoteConfig\Template::getVersion()`
* The parameters of a Remote Config template can now be retrieved with
  `Kreait\Firebase\RemoteConfig\Template::getParameters()`
  
### Changes

#### Cloud Messaging

* `Kreait\Firebase\Messaging::sendMulticast()` now makes full use of the FCM batch API, resulting in substantial
  performance improvements.
* Values passed to `Kreait\Firebase\Messaging\MessageData::withData()` will now be cast to strings instead of
  throwing InvalidArgument exceptions when they are not strings.

## 4.28.0 - 2019-07-29

### Added

#### General

* The SDK is now able to handle connection issues more gracefully. The following exceptions will now be thrown 
  when a connection could not be established:
  * `Kreait\Firebase\Auth\ApiConnectionFailed`
  * `Kreait\Firebase\Database\ApiConnectionFailed`
  * `Kreait\Firebase\Messaging\ApiConnectionFailed`
  * `Kreait\Firebase\RemoteConfig\ApiConnectionFailed`

#### Cloud Messaging

* It is now possible to retrieve extended information about application instances related to a 
  registration token, including the topics an application instance/registration token is subscribed to.
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#app-instance-management))

### Changes

#### General

* Each component now has its own catchable exception interface, e.g. `Kreait\Firebase\Exception\AuthException` or 
  `Kreait\Firebase\Exception\DatabaseException`.
* The following exceptions are now interfaces implemented by specific errors instead of extensible classes:
  * `Kreait\Firebase\Exception\AuthException`
  * `Kreait\Firebase\Exception\DatabaseException` (new)
  * `Kreait\Firebase\Exception\MessagingException`
  * `Kreait\Firebase\Exception\RemoteConfigException`
* `Kreait\Firebase\Auth\CustomTokenViaGoogleIam` is no longer using deprecated methods to build a custom token.
* Getting requests and responses from exceptions is now considered deprecated. If you want to debug HTTP requests,
  use the Firebase factory to debug the HTTP client via configuration or an additional middleware.    

## 4.27.0 - 2019-07-19

### Added

#### Cloud Messaging

* Notifications can now be provided with an image URL
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#adding-a-notification))
* You can use `Kreait\Firebase\Messaging\RawMessageFromArray(array $data)` to create a message
  without the SDK checking it for validity before sending it. This gives you full control over the sent 
  message, but also means that you have to send/validate a message in order to know if it's valid or not.
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#sending-a-fully-configured-raw-message))
* It is now possible to add platform independent FCM options to a message.
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#adding-platform-independent-fcm-options))

### Changes

#### Cloud Messaging

* Removed ability to specify multiple message targets (Condition/Token/Topic) at once when creating an FCM message
  through `CloudMessage::fromArray()`. Previously, only the first matched target was used. 
  Now, an `InvalidArgument` exception is thrown.

## 4.26.0 - 2019-06-23

This is an under-the-hood release and should not affect any existing functionality:

* Internal classes have been marked as `@internal`
* Micro-Optimizations
* PHPStan's analysis level has been set to `max`
* `psr/simple-cache` was implicitely required and is now explicitely required
* Simplified the Travis CI configuration (this should now enably PRs to be tested without errors)

## 4.25.0 - 2019-06-12

* When verifying ID tokens a leeway of 5 minutes is applied when verifying time based claims

## 4.24.0 - 2019-06-10

* You can now send one message to multiple devices with `Kreait\Firebase\Messaging::sendMulticast($message, $deviceTokens)` 
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#send-messages-to-multiple-devices-multicast))

## 4.23.0 - 2019-06-05

* Custom attributes can now be deleted from a user with 
  `Kreait\Firebase\Auth::deleteCustomUserAttributes($uid)` ([#300](https://github.com/kreait/firebase-php/issues/300))

## 4.22.0 - 2019-05-26

* `Kreait\Firebase\Messaging\CloudMessage` can now be created without a target. The existence 
  of a message target is now validated on send. This enables re-using a message for multiple targets.
* Improved reliability of discovering a ServiceAccount from environment variables. 
  (huge thanks to [@Shifu33](https://github.com/Shifu33) for helping to find and test this)
* It is now possible to disable the ServiceAccount discovery by calling 
  `Kreait\Firebase\Factory::withDisabledAutoDiscovery()` ([Documentation](https://firebase-php.readthedocs.io/en/latest/setup.html#disabling-the-autodiscovery))

## 4.21.1 - 2019-05-14

* Fixed return value on `Kreait\Firebase\Database\Transaction::set()`: it returned the HTTP response, but should
  return nothing.

## 4.21.0 - 2019-05-14

* You can now wrap Realtime Database Saves and Deletions in Transactions/Conditional requests. 
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/realtime-database.html#database-transactions)) 
  ([#108](https://github.com/kreait/firebase-php/issues/108))

## 4.20.1 - 2019-04-25

* Fixed `TypeError`s when processing certain API exceptions ([#295](https://github.com/kreait/firebase-php/issues/295))

## 4.20.0 - 2019-03-28

* Sent emails can now be localized by providing a `$locale` parameter to the following methods:
  * `Kreait\Firebase\Auth::sendEmailVerification($uid, $continueUrl = null, $locale = null)`
  * `Kreait\Firebase\Auth::sendPasswordResetEmail($email, $continueUrl = null, $locale = null)`

## 4.19.1 - 2019-03-10

* Improved the error message when encountering an invalid Service Account specification to help developers use
  the correct one (provided by [@puf](https://github.com/puf) in [this StackOverflow answer](https://stackoverflow.com/a/55081397/284325))

## 4.19.0 - 2019-02-09

* When verifying ID tokens, allowed time inconsistencies now include the `auth_time` clime, in addition to the `iat` claim. ([#278](https://github.com/kreait/firebase-php/issues/278))

## 4.18.2 - 2019-01-14

### Bugfixes

* When creating `Kreait\Firebase\Exception\MessagingException` from a `GuzzleHttp\Exception\RequestException`, 
  the HTTP response returned was lost.

## 4.18.1 - 2019-01-14

### Enhancements

* Instances of `Kreait\Firebase\Exception\MessagingException` now have better messages 
  ([#274](https://github.com/kreait/firebase-php/issues/274)).

## 4.18.0 - 2018-10-29

### Enhancements

* `Kreait\Firebase\Messaging\CloudMessage`: You can now create a new message with a different target from an existing message by using the
  `withChangedTarget()` method ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#changing-the-message-target)).

## 4.17.1 - 2018-10-27

### Bugfixes

* The signature of an ID Token is now verified even if a prior error occured (thanks [@kanoblake](https://github.com/kanoblake) for reporting the issue and providing a test case) 

### Enhancements

* ID Tokens must have a valid "auth_time" claim.
* Tokens with an invalid signature now throw a `Firebase\Auth\Token\Exception\InvalidSignature` exception. It extends the previously thrown `Firebase\Auth\Token\Exception\InvalidToken`,
so existing behaviour doesn't change.
* Service Account related errors are now more fine grained.

## 4.17.0 - 2018-09-12

### Changes

* When loading a non-existing/invalid service account file, error details are now included.
* Database rules are now updated with prettified JSON to improve editing them in the web console.

## 4.16.0 - 2018-08-24

### Features

* Added support for Remote Config Template validation ([Documentation](https://firebase-php.readthedocs.io/en/latest/remote-config.html#validation))
* Added support for working with the Remote Config History ([Documentation](https://firebase-php.readthedocs.io/en/latest/remote-config.html#change-history))

## 4.15.1 - 2018-08-07

### Bugfixes

* When on GCP/GCE, the environment was overriding an explicitely injected service account for API request authentication.   

## 4.15.0 - 2018-08-05

### Features

* The SDK can now be used configuration-free on Google Cloud Engine.

## 4.14.0 - 2018-08-05

### Features

* `Kreait\Firebase\Messaging\CloudMessage` can handle all currently supported types of messages and supersedes
  the specialized message types.

### Deprecations

* `Kreait\Firebase\Messaging\MessageToTopic::fromArray()`
  * Use `Kreait\Firebase\Messaging\CloudMessage::fromArray()`
* `Kreait\Firebase\Messaging\MessageToTopic::create($topic)`
  * Use `Kreait\Firebase\Messaging\CloudMessage::withTarget('topic', $topic)`
* `Kreait\Firebase\Messaging\ConditionalMessage::fromArray()`
  * Use `Kreait\Firebase\Messaging\CloudMessage::fromArray()`
* `Kreait\Firebase\Messaging\ConditionalMessage::create($condition)`
  * Use `Kreait\Firebase\Messaging\CloudMessage::withTarget('condition', $condition)`
* `Kreait\Firebase\Messaging\MessageToRegistrationToken::fromArray()`
  * Use `Kreait\Firebase\Messaging\CloudMessage::fromArray()`
* `Kreait\Firebase\Messaging\MessageToRegistrationToken::create($token)`
  * Use `Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $token)`

## 4.13.3 - 2018-07-25

### Bugfixes

* Sanitizing the project ID by default changed the output of the method `\Kreait\Firebase\ServiceAccount::getProjectId()`,
  so a new method `\Kreait\Firebase\ServiceAccount::getSanitizedProjectId()` is now used instead.

## 4.13.2 - 2018-07-25

### Bugfixes

* Project IDs that cannot be used for database URIs directly are sanitized when configuring a Service Account ([#228](https://github.com/kreait/firebase-php/issues/228))

### Non-breaking changes

* When publishing an outdated Remote Config, Firebase previously returned an "OPERATION ABORTED" error,
  resulting in a `Kreait\Firebase\Exception\RemoteConfig\OperationAborted` exception. Firebase has
  changed the error to "VERSION MISMATCH", which now results in a `Kreait\Firebase\Exception\RemoteConfig\VersionMismatch` exception.
  The new exception inherits from the old exception, so no code changes are required.
* A `Kreait\Firebase\Exception\RemoteConfigException` now includes the full error as returned by the Firebase API.

## 4.13.1 - 2018-07-17

### Bugfixes

* Fixed generating a random child key with `$reference->push()->getKey()` ([#222](https://github.com/kreait/firebase-php/issues/222))
* Fixed deleting a reference by setting it to `null` ([#222](https://github.com/kreait/firebase-php/issues/222))

## 4.13.0 - 2018-07-16

### Features

* Added support for setting a continueUrl to email actions ([#220](https://github.com/kreait/firebase-php/pull/220), thanks to [Wade Womersley](https://github.com/wadewomersley))

## 4.12.1 - 2018-07-08

### Bugfixes

* Fixed the import of existing Remote Config conditions ([#218](https://github.com/kreait/firebase-php/issues/218))

### Changes

* A more descriptive exception is thrown when adding a parameter to a Remote Config template that refers 
  to a condition that doesn't exist.

## 4.12.0 - 2018-06-28

* Added support for validating FCM messages without actually sending them ([#216](https://github.com/kreait/firebase-php/issues/216)) 
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#validating-messages))

## 4.11.0 - 2018-06-26

### New features

* Enabled custom configuration and middlewares for the underlying HTTP client
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/setup.html#http-client-options-and-middlewares))

### Bugfixes

* Fixed issue when creating anonymous users: The Firebase REST API now requires empty payloads to be objects, not arrays (`{}` instead of `[]`)

## 4.10.1 - 2018-06-19

### Bugfixes

* Added support for non-alphabetical chars in keys in Database snapshots ([#212](https://github.com/kreait/firebase-php/issues/212))

## 4.10.0 - 2018-06-15

### New features

* Enabled the caching of Google's public keys used for ID Token verification 
  ([#210](https://github.com/kreait/firebase-php/issues/210)) 
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/authentication.html#caching-google-s-public-keys))

## 4.9.0 - 2018-06-09

* Added a flag to `Kreait\Firebase\Auth::verifyIdToken()` to ignore `IssuedInTheFuture` exceptions
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/authentication.html#verify-a-firebase-id-token))

## 4.8.0 - 2018-05-25

### New features

* Added support for FCM Topic management ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#topic-management))
  * `Kreait\Firebase\Messaging::subscribeToTopic($topic, $registrationTokens)`
  * `Kreait\Firebase\Messaging::unsubscribeFromTopic($topic, $registrationTokens)`

### Changes

* Made `Kreait\Firebase\Factory` extensible so that it can be extended by libraries that want to build on it.
  * [morrislaptop/firestore-php](https://github.com/morrislaptop/firestore-php) is a new project that aims to
    provide support for the Firestore without the need to install the `grpc` PHP extension.

## 4.7.1 - 2018-05-09

### Bugfixes

* Fixed marking disabled users as enabled when using arrays ([#196](https://github.com/kreait/firebase-php/issues/196))

## 4.7.0 - 2018-05-08

### New features

* Added support to unlink identity providers from a user
  * `Kreait\Firebase\Auth::unlinkProvider($uid, $provider)` (`$provider` can be a string or an array of strings)
* Added support to remove the phone number from a user ([#195](https://github.com/kreait/firebase-php/issues/195))
  * When you update a user ([Documentation](https://firebase-php.readthedocs.io/en/latest/user-management.html#update-a-user)), you can now 
    * set `phoneNumber` to `null`
    * set `deletePhoneNumber` to `true`
    * set `deleteProvider` to `['phone']`

## 4.6.0 - 2018-04-27

### New features

* Added support for FCM message configurations (Android, APNS, WebPush) (initiated by [@Casperhr](https://github.com/Casperhr), thanks!)

## 4.5.0 - 2018-04-16

### New Features

* Added support for Firebase Cloud Messaging ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html))

### Changes

* Empty properties in a ProviderData object are now filtered out 
  (e.g. the "phone" provider never includes a photo or an email)

## 4.4.0 - 2018-04-07

### New Features

* Added support for setting custom attributes/claims on users
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/user-management.html#set-custom-attributes),
  [Official Firebase documentation](https://firebase.google.com/docs/auth/admin/custom-claims))
  * `Kreait\Firebase\Auth::setCustomUserAttributes($uid, array $attributes)`

### Bugfixes

* Removed PHP 7.0 incompatible `void` return types

## 4.3.0 - 2018-03-29

### New features

* Added support for the Firebase Remote Config ([Documentation](https://firebase-php.readthedocs.io/en/latest/remote-config.html))

## 4.2.3 - 2018-03-27

- Handle non-existing users consistently ([#186](https://github.com/kreait/firebase-php/issues/186))

## 4.2.2 - 2018-03-27

### Bugfix

- Fixed setting email verification flags ([#183](https://github.com/kreait/firebase-php/issues/183)) 

## 4.2.1 - 2018-03-14

### Bugfix

- Renamed method with typo `Storage::getFileystem()` to the correct `Storage::getFilesystem()` 
  ([#182](https://github.com/kreait/firebase-php/issues/182))

## 4.2.0 - 2018-03-08

### New features

* Added support to create and update users with properties 
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/user-management.html#create-a-user))
  * `Kreait\Firebase\Auth::createUser($properties)`
  * `Kreait\Firebase\Auth::updateUser($uid, $properties)`
* Added `Kreait\Firebase\Auth::getUserByPhoneNumber($phoneNumber)`
* Added method to verify the password of an account provided by the email/password provider 
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/user-management.html#verify-a-password))

### Bugfixes

* `Kreait\Firebase\Auth::getUser()` and `Kreait\Firebase\Auth::getUser()` were throwing a TypeError
  when trying to create a user record from an empty data set (the Firebase API returns an empty 
  response when no user is found). Now, a `Kreait\Firebase\Exception\UserNotFound` exception
  is thrown instead.

### Deprecations

* `Kreait\Firebase\Auth\ApiClient::signupNewUser()`
* `Kreait\Firebase\Auth\ApiClient::enableUser()`
* `Kreait\Firebase\Auth\ApiClient::disableUser()`
* `Kreait\Firebase\Auth\ApiClient::changeUserPassword()`
* `Kreait\Firebase\Auth\ApiClient::changeUserEmail()`

## 4.1.2 - 2018-02-25

### Bugfixes

* `Kreait\Firebase\Storage::getFilesystem()` was using/overwriting the configured buckets
* Added simple integration test to ensure that file operations work as excpected

## 4.1.1 - 2018-02-25

### Bugfixes

* Due to improper caching in the Firebase Factory, configuring a new Firebase instance with another 
  service account would have used a wrongly configured storage.

## 4.1.0 - 2018-02-24

### New features

* Added support for the Firebase Cloud Storage
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-storage.html))

## 4.0.2 - 2018-02-22

* Guess incoming timestamps more reliably and ensure UTC Timezones on all returned DateTime properties 

## 4.0.1 - 2018-02-15

### Bugfix

* Fix Undefined index "users" while retrieving list of users ([#168](https://github.com/kreait/firebase-php/pull/168))

## 4.0.0 - 2018-02-14

#### Changes

* All deprecated methods and classes have been removed.
* `Kreait\Firebase\Auth\User` has been replaced with `Kreait\Firebase\Auth\UserRecord`
* All methods that required an instance of `User` now accept UIDs only.
* Added methods
  * `Kreait\Firebase\Auth::getUserByEmail(string $email)`
* Removed methods
  * `Kreait\Firebase\Auth::getUserByEmailAndPassword()`
  * `Kreait\Firebase\Auth::getUserInfo()`

#### Authentication overrides

Since 4.0, defining authentication overrides is only possible when creating a new `Firebase` instance via
the factory (see [Authenticate with limited privileges](https://firebase-php.readthedocs.io/en/latest/authentication.html#authenticate-with-limited-privileges)).
Thus, the following methods have been removed:

  * `Kreait\Firebase::asUser()`
  * `Kreait\Firebase::asUserWithClaims()`
  * `Kreait\Firebase\Database::withCustomAuth()`
  * `Kreait\Firebase\Database\ApiClient::withCustomAuth()`

#### Token generation and verification

The SDK now makes full use of the [kreait/firebase-tokens](https://github.com/kreait/firebase-tokens-php) library and
throws its exceptions when an ID token is considered invalid.

Also, the option to specify a custom expiration time when creating custom tokens has been removed. 
Following the official Firebase SDKs, the lifetime of a custom token is one hour.

Added documentation: 
([Troubleshooting: ID Tokens are issued in the future](https://firebase-php.readthedocs.io/en/latest/troubleshooting.html#id-tokens-are-issued-in-the-future)) 

## 3.9.3 - 2018-01-23

### Bugfixes
* When deleting a user account, an empty account was created with the same UID ([#156](https://github.com/kreait/firebase-php/pull/156))
* Travis CI builds now also work for pull requests

## 3.9.2 - 2018-01-20

### Bugfixes
* A Database API Exception did not always include a request ([#155](https://github.com/kreait/firebase-php/issues/155))

### Other
* Added more integration tests

## 3.9.1 - 2018-01-19

* Reverted deprecations of `Kreait\Firebase\Factory::withTokenHandler()` and `\Kreait\Firebase\Auth\ApiClient::sendEmailVerification()`

## 3.9.0 - 2018-01-19

* Added `Kreait\Firebase\Auth::getUserInfo(string $uid): array`
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/user-management.html#get-information-about-a-specific-user))
* Added `Kreait\Firebase\Auth::disableUser(string $uid)`
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/user-management.html#disable-a-user))
* Added `Kreait\Firebase\Auth::enableUser(string $uid)`
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/user-management.html#enable-a-user))
* Added `Kreait\Firebase\Auth::revokeRefreshTokens(string $uid)`
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/user-management.html#invalidate-user-sessions))
* Added check for revoked ID tokens to `Kreait\Firebase\Auth::verifyIdToken()`
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/authentication.html#verify-a-firebase-id-token))
* Deprecated the usage of the `Kreait\Firebase\Auth\User` object
* Deprecated `Kreait\Firebase\Auth::sendEmailVerification(Kreait\Firebase\Auth\User\User $user)`
* Full rewrite of the [Authentication documentation](https://firebase-php.readthedocs.io/en/latest/authentication.html)

## 3.8.2 - 2018-01-16

* Bugfix: `Kreait\Firebase\Exception\InvalidIdToken` was not able to hold every invalid ID token ([#152](https://github.com/kreait/firebase-php/pull/152))

## 3.8.1 - 2018-01-16

* Bugfix: Ensure that ID tokens are verified fully and completely (discovered by [@hernandev](https://github.com/hernandev), thanks!)

## 3.8.0 - 2018-01-12

* Added `Kreait\Firebase\Auth::listUsers(int $maxResults = 1000, int $batchSize = 1000): \Generator`
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/user-management.html#list-users))
* Auth: Fixed creation of new users (anonymous, email/password)
* Auth: Fixed changing emails and password
* Auth: Removed need for the project's web API key and deprecated related methods
* Added integration tests for database operations
* Restructured tests for clean separation of unit/integration tests
* Enhanced Travis CI build performance
* Ensured support for PHP 7.2

## 3.7.1 - 2018-01-07

* Fixes bug that not more than one custom token could be created at a time.

## 3.7.0 - 2017-12-08

* Enable ordering by nested childs ([#135](https://github.com/kreait/firebase-php/pull/135))

## 3.6.0 - 2017-12-08

* When an ID Token verification has failed, the resulting exception now includes the token.
  ([#139](https://github.com/kreait/firebase-php/issues/139), [#140](https://github.com/kreait/firebase-php/issues/140))

## 3.5.0 - 2017-11-27

* Add support for getting and updating Realtime Database Rules 
  ([#136](https://github.com/kreait/firebase-php/pull/136)) 
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/realtime-database.html#database-rules))
* Handle non-JSON responses better.

## 3.4.2 - 2017-11-08

* Restore PHP 7.0 compatibility

## 3.4.1 - 2017-11-08

* Avoid OutOfBoundsException when a user's email is not set

## 3.4.0 - 2017-11-07

* Added `Kreait\Firebase\Auth\User::getEmail()`
* Added `Kreait\Firebase\Auth\User::hasVerifiedEmail()`
* Added `Kreait\Firebase\Auth::sendPasswordResetEmail($userOrEmail)`
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/user-management.html#send-a-password-reset-email))

## 3.3.3 - 2017-11-01

* Fixed Travis CI builds for pull requests
* Fixed class/namespace collisions in certain PHP versions.

## 3.3.2 - 2017-10-23

* Only classes implementing an interface should be final.

## 3.3.1 - 2017-10-21

* Restored PHP 7.0 compatibility

## 3.3.0 - 2017-10-21

* Enabled API exceptions to be debuggable by including the sent request and received response. 
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/realtime-database.html#debugging-api-exceptions))
  
## 3.2.1 - 2017-10-12

* Reverted `Kreait\Firebase\Factory` deprecations introduced in 3.2.0  

## 3.2.0 - 2017-10-05

* Added user management features ([Documentation](https://firebase-php.readthedocs.io/en/latest/user-management.html))
* Deprecated `Kreait\Firebase\Factory::withServiceAccount()`, use `Kreait\Firebase\Factory::withServiceAccountAndApiKey()` instead 
* Deprecated `Kreait\Firebase::asUserWithClaims()`, use `Kreait\Firebase\Auth::getUser()` and `Kreait\Firebase::asUser()` instead
* Deprecated `Kreait\Firebase::getTokenHandler()`, use `Kreait\Firebase\Auth::createCustomToken()` and `Kreait\Firebase\Auth::verifyIdToken()` instead.
* Added migration instructions for deprecated methods, see [Documentation](https://firebase-php.readthedocs.io/en/latest/migration.html#to-3-2)
  
## 3.1.2 - 2017-08-11

* Removed the restriction to the google/auth package to versions <1.0

## 3.1.1 - 2017-06-17

* Fixed the error that Service Account Autodiscovery was not working when no Discoverer was given.

## 3.1.0 - 2017-06-10

* Deprecated `Kreait\Firebase\Factory::withCredentials()` ([Documentation](https://firebase-php.readthedocs.io/en/latest/migration.html#to-3-1))  
* Extracted Service Account discovery to a distinct component ([Documentation](https://firebase-php.readthedocs.io/en/latest/setup.html#with-autodiscovery))
  * Added `Kreait\Firebase\ServiceAccount::discover()`
  * You can now add your own discovery methods ([Documentation](https://firebase-php.readthedocs.io/en/latest/setup.html#use-your-own-autodiscovery))
* Updated and restructured the documentation 

## 3.0.2 - 2017-06-08

* Added additional checks to ensure given credentials are valid and readable
* When using the Factory and passing the path to an invalid credentials file, the
  factory would continue to try to get the credentials e.g. from one of the
  environment variables. This has now changed: the factory immediately
  quits when given invalid credentials. 

## 3.0.1 - 2017-04-25

* When the credentials file has not been found, a `CredentialsNotFound` exception is thrown,
  including the information which paths have been tried.

## 3.0.0 - 2017-04-22

* Moved all classes inside the `Kreait` namespace to avoid possible conflicts with official Firebase PHP libraries 
  using the `Firebase` namespace.
* Removed database secret authentication, as it has been deprecated by Firebase.

Please visit the [Migration section in the docs](https://firebase-php.readthedocs.io/en/latest/migration.html)
to see which changes in your code are required when upgrading from 2.x to 3.0.

## 2.3.1 - 2017-04-12

* Fixes the problem that it wasn't possible to use startAt/endAt/equalTo with string values.

## 2.3.0 - 2017-04-06

* Allow the usage of a custom token handler when creating a new Firebase instance by adding
  the factory method `withTokenHandler(\Firebase\Auth\Token\Handler $handler)`

## 2.2.0 - 2017-03-14

* Introduce `Firebase\Factory` to create Firebase instances, and deprecate the
  previous static instantiation methods on the `Firebase` class.
  It is now possible to omit an explicit JSON credentials file,
  * if one of the following environment variables is set with the path to the 
    credentials file:
    * `FIREBASE_CREDENTIALS`
    * `GOOGLE_APPLICATION_CREDENTIALS`
  * or if the file is located at
    * `~/.config/gcloud/application_default_credentials.json` (Linux, MacOS)
    * `$APPDATA/gcloud/application_default_credentials.json` (Windows)
* Updated documentation at http://firebase-php.readthedocs.io

## 2.1.3 - 2017-02-23

* Ensure that `guzzlehttp/psr7` 1.4.0 is not used, as it breaks backwards compatibility
  (see [guzzle/psr7#138](https://github.com/guzzle/psr7/issues/138))

## 2.1.2 - 2017-02-19

* Updated [kreait/firebase-tokens](https://github.com/kreait/firebase-tokens-php/releases/tag/1.1.1) 
  to fix #65 (Invalid token when claims are empty).

## 2.1.1 - 2017-02-18

* Updated [kreait/firebase-tokens](https://github.com/kreait/firebase-tokens-php/releases/tag/1.1.0) 
  to make sure ID token verifications continue to work.

## 2.1.0 - 2017-02-07

* Added the means to work with custom tokens and ID tokens by using
  [kreait/firebase-tokens](https://packagist.org/packages/kreait/firebase-tokens). See
  [Authentication: Working with Tokens](http://firebase-php.readthedocs.io/en/latest/authentication.html#working-with-tokens)
  for usage instructions.
* Replaced the implementation of Database Secret based custom tokens (in the `V2` namespace) 
  with a solution based on [`lcobucci/jwt`](https://github.com/lcobucci/jwt) instead of the 
  abandoned [firebase/token-generator](https://github.com/firebase/firebase-token-generator-php).

## 2.0.2 - 2016-12-26

* Added a `SERVER_TIMESTAMP` constant to the `Firebase\Database` class to ease the population of fields
  with [Firebase's timestamp server value](https://firebase.google.com/docs/reference/rest/database/#section-server-values)
  
  ```php
  use Firebase\Database;

  $ref = $db->getReference('my-ref')
            ->set('created_at', Database::SERVER_TIMESTAMP); 
  ```

## 2.0.1 - 2016-12-02

* Rename "Firebase SDK" to "Firebase Admin SDK for PHP" to emphasize the similarity to the [newly
  introduced official Admin SDKs](https://firebase.googleblog.com/2016/11/bringing-firebase-to-your-server.html).
* Added method `Reference::getPath()` to retrieve the full relative path to a node.
* Updated docs to make clearer that authenticating with a Database Secret is not recommended since
  the official deprecation by Firebase (see 
  [the "Database Secrets" section in the "Service Accounts" tab of a project](https://console.firebase.google.com/project/kreait-firebase-php/settings/serviceaccounts/adminsdk)
  )
* It is now possible to pass a JSON string as the Service Account parameter on `Firebase::fromServiceAccount()`.
  Until now, a string would have been treated as the path to a JSON file. 

## 2.0.0 - 2016-11-06

* First stable release

## 2.0.0-beta3 - 2016-11-05

* A `PermissionDenied` exception is thrown when a request violates the 
  [Firebase Realtime Database rules](https://firebase.google.com/docs/database/security/securing-data)
* An `IndexNotDefined` exception is thrown when a Query is performed on an unindexed subtree
* Removes the query option to sort results in descending order.
  * Nice in theory, conflicted in practice: when combined with `limitToFirst()` or `limitToLast()`,
    results were lost because Firebase sorts in ascending order and limits the results before
    we can process them further.
* Adds a new Method `Reference::getChildKeys()` to retrieve the key names of a reference's children
  * This is a convenience method around a shallow query, see 
    [shallow queries in the Firebase docs](https://firebase.google.com/docs/database/rest/retrieve-data#shallow)

## 2.0.0-beta2 - 2016-10-11

* Adds documentation for Version 2.x at http://firebase-php.readthedocs.io/
* Allows the database URI to be overriden when creating a Firebase instance through the factory

## 2.0.0-beta1 - 2016-08-14

* Rewrite, beta status due to missing documentation for the new version.

## 1.x

* The changelog for version 1.x can be found here:
  https://github.com/kreait/firebase-php/blob/1.x/CHANGELOG.md
