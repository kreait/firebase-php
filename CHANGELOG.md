# CHANGELOG

## [Unreleased]
### Notes
* Remote Config templates now support up to 3000 parameters (instead of up to 2000 parameters)

## [5.21.0] - 2021-07-16
### Added
* Added support for Session Cookie Generation
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/authentication.html#session-cookies))

### Changes
* Bumped `kreait/firebase-tokens` to `^1.16`
* Updated version constraints of `psr/cache` to allow newer releases
* Updated version constraints of `psr/log` to allow newer releases

## [5.20.1] - 2021-05-12
### Fixed
* Restored broken support for Guzzle 6.x

## [5.20.0] - 2021-05-11
* Dropped support for unsupported PHP versions. Dropped support for unsupported PHP versions. Starting with this 
  release, supported are PHP versions >=7.4.

## [5.19.0] - 2021-05-09
### Added
* Added the `startAfter` and `endBefore` filters for the Realtime Database. At the moment they
  don't seem to have an effect on the returned results (just as if they didn't exist); it's
  unclear if the implementation is incorrect or if the REST API doesn't support the new
  filters yet. If you see why it's not working or if it _does_ work for you, please
  let me know.
### Changed
* `CloudMessage::withData()` allowed the message data to be empty, resulting in the Firebase
  API rejecting the message. If the message data is empty, the field is now removed before
  sending the message.
  ([#591](https://github.com/kreait/firebase-php/issues/591))

## [5.18.0] - 2021-04-19
### Added
* Added support for more public keys from Google that ID Tokens could have been signed with. 

## [5.17.1] - 2021-04-13
### Fixed
* [5.16.0] introduced a check for reserved words and prefixes in FCM Data Payloads - although stated
  otherwise in the official documentation, the keyword `notification` is _not_ be rejected by the
  Firebase API, causing projects to break that used it and updated the SDK. This release removes
  the check for this key.

## [5.17.0] - 2021-03-21
### Added
* Helper methods to specify a message priority
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#message-priority))
### Changed
* `giggsey/libphonenumber-for-php` is now an _optional_ dependency instead of a required one. It can be used
  to validate a phone number before sending it to the Firebase Servers, where an invalid phone number will
  be rejected anyway. If you want to continue using the "pre"-validation, please add the library to your
  project's direct dependencies, e.g. with `composer require "giggsey/libphonenumber-for-php:^8.9"`.
  ([#577](https://github.com/kreait/firebase-php/discussions/577))

## [5.16.0] - 2021-03-07
### Fixed
* It was not possible to send password reset emails to users belonging to a tenant. 
  ([#573](https://github.com/kreait/firebase-php/issues/573))
### Changed
* FCM Data Payloads are now checked for reserved words and prefixes, according to the
  [FCM Data Messages Documentation](https://firebase.google.com/docs/cloud-messaging/concept-options#data_messages).
  Reserved words include "from", "notification," "message_type", or any word starting with "google" or "gcm."
  Instead of throwing an exception after the FCM API has rejected a message, the exception will no be thrown 
  _before_ sending the message. 
  ([#574](https://github.com/kreait/firebase-php/issues/574))

## [5.15.0] - 2021-03-01
### Added
* All main components of the SDK are now based on Interfaces in the `Kreait\Firebase\Contract` namespace. 
  This should enable projects implementing the SDK to mock the components more easily (Note: the
  `Kreait\Firebase\Factory` class is not provided as a contract, and you should not rely 
  on it in your tests).
  
  The added contracts are:
  * `\Kreait\Firebase\Contract\Auth`
  * `\Kreait\Firebase\Contract\Database`
  * `\Kreait\Firebase\Contract\DynamicLinks`
  * `\Kreait\Firebase\Contract\Firestore`
  * `\Kreait\Firebase\Contract\RemoteConfig`
  * `\Kreait\Firebase\Contract\Storage`

### Changed
* More explanatory error messages when
  * a requested Realtime Database instance could not be reached
  * an FCM target device is not known to the current project

## [5.14.1] - 2020-12-31
### Fixed
* Fixed handling of rejected promises in the App Instance API Client
  ([#536](https://github.com/kreait/firebase-php/issues/536))

## [5.14.0] - 2020-12-13
### Added
* Single reports of a `MulticastSendReport` now include the sent message, in addition to the response.
* It is now possible to validate multiple messages at once by adding a parameter to the `send*` Methods
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#validating-messages))
* It is now possible to check a list of registration tokens whether they are valid and known, unknown, or invalid
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#validating-registration-tokens))
* Added methods:
  * `Kreait\Firebase\Messaging::validateRegistrationTokens($registrationTokenOrTokens)`
### Deprecated
* `Kreait\Firebase\Http\Requests::findBy()`
* `Kreait\Firebase\Messaging\MulticastSendReport::withAdded()`
### Fixed
* 5.13 introduced a bug which caused expired ID tokens not to be rejected as invalid. 
  [#526](https://github.com/kreait/firebase-php/issues/526)

## [5.13.0] - 2020-12-10

This release ensures compatibility with PHP 8.0

## [5.12.0] - 2020-11-27
### Added
* The Auth component is now tenant-aware.
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/authentication.html#tenant-awareness))
* Added methods
  * `\Kreait\Firebase\RemoteConfig\Parameter::description()`
### Fixed
* Fix usage of deprecated functionality from lcobucci/jwt

## [5.11.0] - 2020-11-01
### Added
* Added helper methods to add default/specific notification sounds to messages
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#notification-sounds))
  * `Kreait\Firebase\Messaging\ApnsConfig::withDefaultSound()`
  * `Kreait\Firebase\Messaging\ApnsConfig::withSound($sound)`
  * `Kreait\Firebase\Messaging\AndroidConfig::withDefaultSound()`
  * `Kreait\Firebase\Messaging\AndroidConfig::withSound($sound)`
  * `Kreait\Firebase\Messaging\CloudMessage::withDefaultSounds()`
* Added exception handler for FCM errors concerning quota/rate limits. When a quota is exceeded, a
  `Kreait\Firebase\Exception\Messaging\QuotaExceeded` exception is thrown. You can get the
  datetime after which to retry with `Kreait\Firebase\Exception\Messaging\QuotaExceeded::retryAfter()`
* When the Firebase API is unavailable and/or overloaded, the response might return a `Retry-After`
  header. When it does, you can get the datetime after which it is suggested to retry with
  `Kreait\Firebase\Exception\Messaging\ServerUnavailable::retryAfter()`
* Added support for the retrieval of user's last activity time with `Kreait\Firebase\Auth\UserMetadata::$lastRefreshedAt`
### Fixed
* `Kreait\Firebase\Messaging\CloudMessage::fromArray()` did not allow providing pre-configured message components
  (objects instead of "pure" arrays)

## [5.10.0] - 2020-10-20
### Added
* Added `Kreait\Firebase\Auth::getUsers()` enables retrieving multiple users at once.
  ([#477](https://github.com/kreait/firebase-php/pull/477))
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/user-management.html#get-information-about-multiple-users))
* Added support to sign in with Twitter OAuth credentials
  ([#481](https://github.com/kreait/firebase-php/pull/481))
* Added convenience method to sign in with IDP credentials ([Documentation](https://firebase-php.readthedocs.io/en/latest/authentication.html#sign-in-with-idp-credentials)):
  * `Kreait\Firebase\Auth::signInWithTwitterOauthCredential($accessToken, $oauthTokenSecret)`
  * `Kreait\Firebase\Auth::signInWithGoogleIdToken($idToken)`
  * `Kreait\Firebase\Auth::signInWithFacebookAccessToken($accessToken)`
* It is now possible to add/remove multiple topic subscriptions for multiple registration tokens.
  (Previously, you could already work with multiple registration tokens, but only on single message topics).
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html#topic-management))
  * `Kreait\Firebase\Messaging::subscribeToTopics($topics, $registrationTokenOrTokens)` 
  * `Kreait\Firebase\Messaging::unsubscribeFromTopics($topics, $registrationTokenOrTokens)` 
  * `Kreait\Firebase\Messaging::unsubscribeFromAllTopics($registrationTokenOrTokens)`
* The RemoteConfig component now support Parameter Groups.
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/remote-config.html#parameter-groups))
* Added methods allowing to use the email address associated with OOB password resets.
  ([#482](https://github.com/kreait/firebase-php/pull/482), [#485](https://github.com/kreait/firebase-php/pull/485))
  * `Kreait\Firebase\Auth::verifyPasswordResetCodeAndReturnEmail(string $oobCode)`
  * `Kreait\Firebase\Auth::confirmPasswordResetAndReturnEmail(string $oobCode, $newPassword, bool $invalidatePreviousSessions = true)`
### Changed
  * Replaced usage of deprecated Guzzle helpers
### Deprecated
  * `Kreait\Firebase\RemoteConfig\Parameter::fromArray()`
  * `Kreait\Firebase\RemoteConfig\Template::fromResponse()`

## [5.9.0] - 2020-10-04
### Added
* PHP `^8.0` is now an allowed (but untested) PHP version

## [5.8.1] - 2020-09-05
### Fixed
* The `HttpClientOptions` introduced in 5.8.0 caused a misconfiguration in the underlying
  HTTP Client by trying to be too fancy (I'm sorry). 
  ([#466](https://github.com/kreait/firebase-php/issues/466))
  
  _This is technically a breaking change because the return type of some public methods
  of the `HttpClientOptions` has changed - but since they are meant to be used for service
  creation, it is very unlikely that they have been used outside the SDK internals, so
  the risk of breaking an existing application with this change is so low, that I'll
  take the risk of getting shouted at for it._ 

## [5.8.0] - 2020-08-23
### Added
* It is now possible to remove emails from users in the auth database.
  ([#459](https://github.com/kreait/firebase-php/issues/459)).
* You can configure the behavior of the HTTP Client performing the API 
  requests by passing an instance of `Kreait\Firebase\Http\HttpClientOptions` 
  to the factory before creating a service.
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/setup.html#http-client-options))

## [5.7.0] - 2020-07-19
### Added
* Added `Kreait\Firebase\RemoteConfig\DefaultValue` now has an added `value()` method to 
  retrieve a default value's value.
* When a given service account could not be processed, the error message now includes 
  more details.

## [5.6.0] - 2020-07-02
### Added
* User Records now contain the date and time when a user's password has last been updated.
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/user-management.html#user-records))
### Changed
* Message data added to a with `CloudMessage::withData()` now rejects binary data to avoid broken
  messages being sent to the Firebase API.
  ([#441](https://github.com/kreait/firebase-php/issues/441))
### Fixed
* It was not possible to instantiate a Custom Token Generator on GAE/GCE due to missing
  auto discovery.

## [5.5.0] - 2020-06-19
### Added
* It is now possible to log outgoing HTTP requests and responses to the Firebase APIs. 
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/setup.html#logging))
### Changed
* `Kreait\Firebase\Factory::withEnabledDebug()` now accepts an instance of 
  `Psr\Log\LoggerInterface` as parameter to log HTTP messages.
### Deprecated
* Calling `Kreait\Firebase\Factory::withEnabledDebug()` without a Logger continues to enable Guzzle's
  default debug behaviour to print HTTP debug output to STDOUT, but will trigger a deprecation notice suggesting using a Logger instead.

## [5.4.0] - 2020-06-09
### Added
* `Kreait\Firebase\Auth::setCustomUserClaims()` as a replacement for `Kreait\Firebase\Auth::setCustomUserAttributes()`
  and `Kreait\Firebase\Auth::deleteCustomUserAttributes()`
* `Kreait\Firebase\Auth\UserRecord::$customClaims` as a replacement for 
  `Kreait\Firebase\Auth\UserRecord::$customAttributes`
### Changed
* The default branch of the GitHub repository has been renamed from `master` to `main` - if you're using `dev-master`
  as a version constraint in your `composer.json`, please update it to `dev-main`.
### Deprecated
* `Kreait\Firebase\Auth::setCustomUserAttributes()`
* `Kreait\Firebase\Auth\UserRecord::$customAttributes`
### Fixed
* Exceptions thrown by the Messaging component did not include the previous ``RequestException`` 
  ([#428](https://github.com/kreait/firebase-php/issues/428))

## [5.3.0] - 2020-05-27
### Changed
* In addition to with `getenv()`, the SDK now looks for environment variables in `$_SERVER` and `$_ENV` as well. 

## [5.2.0] - 2020-05-03
### Added
* It is now possible to retrieve the Firebase User ID directly from a `SignInResult` after a successful user sign-in 
  with `SignInResult::firebaseUserId()`

## [5.1.1] - 2020-04-16
### Fixed
* Custom Token Generation was not possible with an auto-discovered Service Account 
  ([#412](https://github.com/kreait/firebase-php/issues/412)) 

## [5.1.0] - 2020-04-06
### Added
* Fetched authentication tokens (to authenticate requests to the Firebase API) are now cached in-memory by default
  ([#404](https://github.com/kreait/firebase-php/issues/404)) 

## [5.0.0] - 2020-04-01
**If you are not using any classes or methods marked as `@deprecated` or `@internal` you should be able 
to upgrade from a 4.x release to 5.0 without changes to your code.**
### Removed
* Support for PHP `<7.2`
* Deprecated methods and classes

[Unreleased]: https://github.com/kreait/firebase-php/compare/5.21.0...HEAD
[5.21.0]: https://github.com/kreait/firebase-php/compare/5.20.1...5.21.0
[5.20.1]: https://github.com/kreait/firebase-php/compare/5.20.0...5.20.1
[5.20.0]: https://github.com/kreait/firebase-php/compare/5.19.0...5.20.0
[5.19.0]: https://github.com/kreait/firebase-php/compare/5.18.0...5.19.0
[5.18.0]: https://github.com/kreait/firebase-php/compare/5.17.1...5.18.0
[5.17.1]: https://github.com/kreait/firebase-php/compare/5.17.0...5.17.1
[5.17.0]: https://github.com/kreait/firebase-php/compare/5.16.0...5.17.0
[5.16.0]: https://github.com/kreait/firebase-php/compare/5.15.0...5.16.0
[5.15.0]: https://github.com/kreait/firebase-php/compare/5.14.1...5.15.0
[5.14.1]: https://github.com/kreait/firebase-php/compare/5.14.0...5.14.1
[5.14.0]: https://github.com/kreait/firebase-php/compare/5.13.0...5.14.0
[5.13.0]: https://github.com/kreait/firebase-php/compare/5.12.0...5.13.0
[5.12.0]: https://github.com/kreait/firebase-php/compare/5.11.0...5.12.0
[5.11.0]: https://github.com/kreait/firebase-php/compare/5.10.0...5.11.0
[5.10.0]: https://github.com/kreait/firebase-php/compare/5.9.0...5.10.0
[5.9.0]: https://github.com/kreait/firebase-php/compare/5.8.1...5.9.0
[5.8.1]: https://github.com/kreait/firebase-php/compare/5.8.0...5.8.1
[5.8.0]: https://github.com/kreait/firebase-php/compare/5.7.0...5.8.0
[5.7.0]: https://github.com/kreait/firebase-php/compare/5.6.0...5.7.0
[5.6.0]: https://github.com/kreait/firebase-php/compare/5.5.0...5.6.0
[5.5.0]: https://github.com/kreait/firebase-php/compare/5.4.0...5.5.0
[5.4.0]: https://github.com/kreait/firebase-php/compare/5.3.0...5.4.0
[5.3.0]: https://github.com/kreait/firebase-php/compare/5.2.0...5.3.0
[5.2.0]: https://github.com/kreait/firebase-php/compare/5.1.1...5.2.0
[5.1.1]: https://github.com/kreait/firebase-php/compare/5.1.0...5.1.1
[5.1.0]: https://github.com/kreait/firebase-php/compare/5.0.0...5.1.0
[5.0.0]: https://github.com/kreait/firebase-php/compare/4.44.0...5.0.0
