# CHANGELOG

## [Unreleased]

This is a release with breaking changes. Please review the following changes and adapt your application where needed.

The supported way to interact with the SDK is to instantiate Components with the `Kreait\Firebase\Factory::create*`
methods.

### Setup
* It is now mandatory to provide a Firebase Project ID. When the project ID cannot be determined from the
  provided credentials (usually a service account), it can be provided by setting the `GOOGLE_CLOUD_PROJECT=<project-id>`
  environment variable.
* The environment variable `FIREBASE_CREDENTIALS` will not be evaluated anymore for credentials auto-discovery. If you
  rely on auto-discovery, use the `GOOGLE_APPLICATION_CREDENTIALS` environment variable. This was already supported in
  earlier versions and is the same environment variable the official Google Libraries use.
* All components have been made `final` and marked as `@internal`, if you're type-hinting dependencies in your
  application code, make sure you type-hint the `Kreait\Firebase\Contract\*` **interfaces**, not the
  `Kreait\Firebase\*` **implementations**

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

[Unreleased]: https://github.com/kreait/firebase-php/compare/5.x...6.x
