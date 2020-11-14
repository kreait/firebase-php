# CHANGELOG

## [Unreleased]
### Added
* `\Kreait\Firebase\RemoteConfig\Parameter::description()`

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

[Unreleased]: https://github.com/kreait/firebase-php/compare/5.11.0...HEAD
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
