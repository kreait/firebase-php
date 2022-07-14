# CHANGELOG

## [Unreleased]

## [6.6.1] - 2022-07-12

### Fixed

* The `WebPushConfig` class is now more lenient with TTL values, and urgencies are checked if they are valid
  ([#716](https://github.com/kreait/firebase-php/issues/716))
* The `AndroidConfig` didn't allow the TTL to be `null`)
  ([#719](https://github.com/kreait/firebase-php/issues/719))

## [6.6.0] - 2022-07-07

### Fixed

* The `AndroidConfig` class is now more lenient with TTL values
  ([#713](https://github.com/kreait/firebase-php/issues/713))

### Added

* The maximum amount of messages that can be sent in batches can be accessed 
  `Kreait\Firebase\Contract\Messaging::BATCH_MESSAGE_LIMIT`

### Deprecated

* `Kreait\Firebase\Messaging\Http\Request\SendMessages::MAX_AMOUNT_OF_MESSAGES`
* `Kreait\Firebase\Messaging\Http\Request\SendMessageToTokens::MAX_AMOUNT_OF_TOKENS`

## [6.5.1] - 2022-06-27

### Fixed

* Keys in the data payload of an FCM message were always lower-cased, although they shouldn't have been.
  ([#709](https://github.com/kreait/firebase-php/issues/709)

## [6.5.0] - 2022-06-22

### Added

* Problems while fetching Dynamic Link statistics now result in more helpful exception messages.
  ([#707](https://github.com/kreait/firebase-php/issues/707)

### Changed

* Raised minimum version of Guzzle to address [CVE-2022-31090](https://github.com/advisories/GHSA-25mq-v84q-4j7r)
  and [CVE-2022-31091](https://github.com/advisories/GHSA-q559-8m2m-g699)

## [6.4.1 - 2022-06-15]

### Fixed

* Updating a Realtime Database Ruleset converted lists to objects with numeric keys.
  ([#706](https://github.com/kreait/firebase-php/pull/706))

### Changed

* Raised minimum version of Guzzle to address [CVE-2022-31042](https://github.com/advisories/GHSA-f2wf-25xc-69c9)

## [6.4.0] - 2022-06-08

### Added

* If not already set, APNs configs are enriched with the necessary headers and fields to ensure the delivery of
  iOS background messages and alerts.
  * The `apns-push-type` header is set to `background` or `alert`
  * The `content-available` field is set to `1` in case of a background message
* FCM Messages are now annotated for better PHPStan/Psalm resolution
* Added methods
  * `\Kreait\Firebase\Messaging\AndroidConfig::withMinimalNotificationPriority()`
  * `\Kreait\Firebase\Messaging\AndroidConfig::withLowNotificationPriority()`
  * `\Kreait\Firebase\Messaging\AndroidConfig::withDefaultNotificationPriority()`
  * `\Kreait\Firebase\Messaging\AndroidConfig::withHighNotificationPriority()`
  * `\Kreait\Firebase\Messaging\AndroidConfig::withMaximalNotificationPriority()`
  * `\Kreait\Firebase\Messaging\AndroidConfig::withNotificationPriority()`
  * `\Kreait\Firebase\Messaging\AndroidConfig::withUnspecifiedNotificationPriority()`
  * `\Kreait\Firebase\Messaging\AndroidConfig::withPrivateNotificationVisibility()`
  * `\Kreait\Firebase\Messaging\AndroidConfig::withPublicNotificationVisibility()`
  * `\Kreait\Firebase\Messaging\AndroidConfig::withSecretNotificationVisibility()`
  * `\Kreait\Firebase\Messaging\AndroidConfig::withNotificationVisibility()`
  * `\Kreait\Firebase\Messaging\ApnsConfig::data()`
  * `\Kreait\Firebase\Messaging\ApnsConfig::hasHeader()`
  * `\Kreait\Firebase\Messaging\ApnsConfig::isAlert()`
  * `\Kreait\Firebase\Messaging\ApnsConfig::toArray()`
  * `\Kreait\Firebase\Messaging\ApnsConfig::withApsField()`
  * `\Kreait\Firebase\Messaging\ApnsConfig::withDataField()`
  * `\Kreait\Firebase\Messaging\ApnsConfig::withHeader()`

### Changed

* FCM notifications (`Kreait\Firebase\Messaging\Notification`) can now be created with null values. 
  If a notification has _only_ null values, the notification payload will be removed on 
  serialization as if it wasn't provided at all.
* Deprecations
  * `\Kreait\Firebase\Messaging\AndroidConfig::withHighPriority()`, 
    use `\Kreait\Firebase\Messaging\AndroidConfig::withHighMessagePriority()` instead
  * `\Kreait\Firebase\Messaging\AndroidConfig::withNormalPriority()`, 
    use `\Kreait\Firebase\Messaging\AndroidConfig::withNormalMessagePriority()` instead
  * `\Kreait\Firebase\Messaging\AndroidConfig::withPriority()`, 
    use `\Kreait\Firebase\Messaging\AndroidConfig::withMessagePriority()` instead

## [6.3.1] - 2022-05-07

### Fixed

* Nested lists in custom user claims were not correctly encoded. 
  ([#699](https://github.com/kreait/firebase-php/pull/699))

## [6.3.0] - 2022-04-24

### Added

* Added support for the Firebase Auth Emulator.
  ([#692](https://github.com/kreait/firebase-php/pull/692)) ([Documentation](https://firebase-php.readthedocs.io/en/latest/testing.html))
* Tenant aware session cookie handling is now officially supported.

## [6.2.0] - 2022-03-03

### Added

* Cloud Messaging: Added support for APNS subtitles (supported by iOS 9+, silently ignored for others)
  ([#692](https://github.com/kreait/firebase-php/pull/692))
* Auth: In `Auth::listUsers()`, if the specified batch size exceeds the specified maximum number of
  to be returned users, the batch size will be reduced from the default 1000. As an example: previously,
  `Auth::listUsers(2)` would have downloaded 1000 accounts (the default batch size), but return only
  the first two. After the change, only two accounts will be downloaded.
* Added methods
  * `Kreait\Firebase\Messaging\ApnsConfig::withSubtitle()`

### Changed

* Replaced internal JSON helper class with [`beste/json`](https://github.com/beste/json)
* Deprecated classes
  * `Kreait\Firebase\Util\JSON`

## [6.1.0] - 2022-01-28

### Added

* Added convenience method to bulk-remove multiple children of an RTDB Reference
  ([#686](https://github.com/kreait/firebase-php/pull/686))
* Added support for Session Cookie Verification.
  * Tenants don't seem to be supported at the moment (creating or verifying a Session Cookie with a tenant-enabled 
    Firebase project yields an `UNSUPPORTED_TENANT_OPERATION` error), but once it _is_ supported, the SDK will need
    no or just minimal updates.
    Integration tests are in place to checking for this error so that we know early on when it starts working.
* Added methods:
  * `Kreait\Firebase\Auth::verifySessionCookie()` 
  * `Kreait\Firebase\Database\Reference::removeChildren()`

### Changed
* Tenant-aware auth methods are now tested the same way as tenant-unaware methods. In order to achieve this,
  some internal implementations had to be changed, warranting this minor version bump. Please note that
  the tests uncovered that creating session tokens is currently not possible when working with tenants.
  ([GitHub issue](https://github.com/firebase/firebase-admin-python/issues/577) /
  [Google Issue Tracker issue](https://issuetracker.google.com/issues/204377229)))
* Deprecated classes
  * `Kreait\Firebase\Auth\CreateActionLink\ApiRequest`
  * `Kreait\Firebase\Auth\CreateSessionCookie\ApiRequest`
  * `Kreait\Firebase\Auth\SendActionLink\ApiRequest`

## [6.0.1] - 2022-01-16

### Fixed
* When signing in with IdP credentials a user's Firebase UID is retrieved from the returned `localId` field, if present

## [6.0.0] - 2022-01-07

This is a release with breaking changes. Please review the following changes and adapt your application where needed.

The supported way to interact with the SDK is to instantiate Components with the `Kreait\Firebase\Factory::create*`
methods.

### Setup
* It is now mandatory to provide a Firebase Project ID. When the project ID cannot be determined from the
  provided credentials (usually a service account), it can be provided by setting the `GOOGLE_CLOUD_PROJECT=<project-id>`
  environment variable or by calling `$factory = $factory->withProjectId('project-id')`.
* The environment variable `FIREBASE_CREDENTIALS` will not be evaluated anymore for credentials auto-discovery. If you
  rely on auto-discovery, use the `GOOGLE_APPLICATION_CREDENTIALS` environment variable. This was already supported in
  earlier versions and is the same environment variable the official Google Libraries use.
* All components have been made `final` and marked as `@internal`, if you're type-hinting dependencies in your
  application code, make sure you type-hint the `Kreait\Firebase\Contract\*` **interfaces**, not the
  `Kreait\Firebase\*` **implementations**
* `Kreait\Firebase\Factory` has been locked down. It should only be used to configure and retrieve the services
  provided by the SDK as documented. The method `Kreait\Firebase\Factory::createApiClient()` will provide you with
  an authorized Guzzle HTTP Client that you can use for custom API operations.
* `Kreait\Firebase\Factory::withVerifierCache()` now expects a PSR-6 Cache Item Pool and doesn't directly support
  PSR-16 Caches anymore. If you only have a PSR-16 Cache available in your project, you can use an adapter, e.g.
  one provided by the `symfony/cache` component. If you're using the Firebase Bundle or the Laravel Package, this
  will be taken care of once they are updated to use the new release.

### Auth component
* `Kreait\Firebase\Contract\Auth::parseToken()` and `Kreait\Firebase\Contract\Auth::verifyIdToken()` now return
  an instance of `Lcobucci\JWT\UnencryptedToken` instead of `\Lcobucci\JWT\Token` - this ensures access to its
  `getClaims()` method.
* `Kreait\Firebase\Contract\Auth::verifyIdToken()` now accepts an optional third parameter, `$leewayInSeconds`, to
  specify the number of seconds a token is allowed to be expired, in case that there is a clock skew between the signing
  and the verifying server. **The previous default of a 300 seconds leeway has been removed**, if you want to restore
  the previous behavior, call the method with the third parameter set: `verifyIdToken($token, false, 300)`
* `Kreait\Firebase\Contract\Auth::verifyIdToken()` will now throw either
  `Kreait\Firebase\Exception\Auth\FailedToVerifyToken` when the verification failed, or
  `Kreait\Firebase\Exception\Auth\RevokedIdToken` when the token has been revoked.
* `Kreait\Firebase\Contract\Auth::verifyPasswordResetCode()` now returns the email address the password was reset for.
  * This was previously done with the method `Kreait\Firebase\Contract\Auth::verifyPasswordResetCodeAndReturnEmail()` -
    this method has been removed.
* `Kreait\Firebase\Contract\Auth::confirmPasswordReset()` now returns the email address the password reset was confirmed for.
    * This was previously done with the method `Kreait\Firebase\Contract\Auth::confirmPasswordResetAndReturnEmail()` -
      this method has been removed.
* The following methods were shortcuts for `Kreait\Firebase\Contract\Auth::signInWithIdpAccessToken()` and
  `Kreait\Firebase\Contract\Auth::signInWithIdpIdToken()` and have been removed.
  * `Kreait\Firebase\Contract\Auth::signInWithAppleIdToken()`, use `signInWithIdpIdToken('apple.com', ...)` instead
  * `Kreait\Firebase\Contract\Auth::signInWithFacebookAccessToken()`, use `signInWithIdpAccessToken('facebook.com', ...)` instead
  * `Kreait\Firebase\Contract\Auth::signInWithGoogleIdToken()`, use `signInWithIdpIdToken('google.com', ...)` instead
  * `Kreait\Firebase\Contract\Auth::signInWithTwitterOauthCredential()`, use `signInWithIdpAccessToken('twitter.com', ...)` instead
* The following methods now return strings instead of value objects:
    * `Kreait\Firebase\Contract\Auth::confirmPasswordReset()`
    * `Kreait\Firebase\Contract\Auth::verifyPasswordResetCode()`
    * `Kreait\Firebase\RemoteConfig\User::email()`
* The following classes are mainly used for validation and have been marked internal. They shouldn't be used directly,
  as they could be updated with breaking changes or get removed entirely in the future.
    * `Kreait\Firebase\Value\ClearTextPassword`
    * `Kreait\Firebase\Value\Email`
    * `Kreait\Firebase\Value\Uid`
    * `Kreait\Firebase\Value\Url`

### Realtime Database Component
* The constant `Kreait\Firebase\Database::SERVER_TIMESTAMP` has been moved to `Kreait\Firebase\Contract\Database::SERVER_TIMESTAMP`

### Other
* Dropped support for Guzzle <7.0
* Dropped support for `lcobucci/jwt` <4.1
* Removed local phone number validation when `giggsey/libphonenumber-for-php` was installed. Phone numbers are
  validated by the Firebase Service in any case, and even when a phone number was considered valid, in rare
  cases the Firebase API rejected them still.
* Replaced `kreait/clock` with `beste/clock`, which implements the proposed [PSR-20 Clock Interface](https://github.com/php-fig/fig-standards/blob/master/proposed/clock.md).
* The following classes and methods have been removed:
  * `Kreait\Firebase\Auth\ActionCodeSettings\RawActionCodeSettings`
  * `Kreait\Firebase\Project\ProjectId`
  * `Kreait\Firebase\Value\Provider`
  * `Kreait\Firebase\Project\TenantId`
  * `Kreait\Firebase\Auth::setCustomUserAttributes()`, use `Kreait\Firebase\Auth::setCustomUserClaims()` instead
  * `Kreait\Firebase\Auth::deleteCustomUserAttributes()`, use `Kreait\Firebase\Auth::setCustomUserClaims()` with null values instead
  * `Kreait\Firebase\Contract\Auth::verifyPasswordResetCodeAndReturnEmail()`, use `Kreait\Firebase\Contract\Auth::verifyPasswordResetCode()` instead 
  * `Kreait\Firebase\Contract\Auth::confirmPasswordResetAndReturnEmail()`, use `Kreait\Firebase\Contract\Auth::confirmPasswordReset()` instead 
  * `Kreait\Firebase\Auth\UserRecord::$customAttributes`, use `Kreait\Firebase\Auth\UserRecord::$customClaims` instead
  * `Kreait\Firebase\Factory::withEnabledDebug()`, use `Kreait\Firebase\Factory::withHttpDebugLogger()` instead
* The following classes are mainly used for validation and have been marked internal. They shouldn't be used directly,
  as they could be updated with breaking changes or get removed entirely in the future.
    * `Kreait\Firebase\Value\ClearTextPassword`
    * `Kreait\Firebase\Value\Email`
    * `Kreait\Firebase\Value\Uid`
    * `Kreait\Firebase\Value\Url`

[Unreleased]: https://github.com/kreait/firebase-php/compare/6.6.1...6.x
[6.6.1]: https://github.com/kreait/firebase-php/compare/6.6.0...6.6.1
[6.6.0]: https://github.com/kreait/firebase-php/compare/6.5.1...6.6.0
[6.5.1]: https://github.com/kreait/firebase-php/compare/6.5.0...6.5.1
[6.5.0]: https://github.com/kreait/firebase-php/compare/6.4.1...6.5.0
[6.4.1]: https://github.com/kreait/firebase-php/compare/6.4.0...6.4.1
[6.4.0]: https://github.com/kreait/firebase-php/compare/6.3.1...6.4.0
[6.3.1]: https://github.com/kreait/firebase-php/compare/6.3.0...6.3.1
[6.3.0]: https://github.com/kreait/firebase-php/compare/6.2.0...6.3.0
[6.2.0]: https://github.com/kreait/firebase-php/compare/6.1.0...6.2.0
[6.1.0]: https://github.com/kreait/firebase-php/compare/6.0.1...6.1.0
[6.0.1]: https://github.com/kreait/firebase-php/compare/6.0.0...6.0.1
[6.0.0]: https://github.com/kreait/firebase-php/compare/5.x...6.0.0
