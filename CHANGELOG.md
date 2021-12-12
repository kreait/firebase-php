# CHANGELOG

## [Unreleased]
### Changed
* It is now mandatory to provide a Firebase Project ID. When the project ID cannot be determined from the
  provided credentials (usually a service account), it can be provided by setting the `GOOGLE_CLOUD_PROJECT=<project-id>`
  environment variable.
* The following methods now return strings instead of value objects:
    * `Kreait\Firebase\Contract\Auth::confirmPasswordResetAndReturnEmail()`
    * `Kreait\Firebase\Contract\Auth::verifyPasswordResetCodeAndReturnEmail()`
    * `Kreait\Firebase\RemoteConfig\User::email()`
* The following classes are mainly used for validation and have been marked internal. They shouldn't be used directly,
  as they could be updated with breaking changes or get removed entirely in the future.
    * `Kreait\Firebase\Value\ClearTextPassword`
    * `Kreait\Firebase\Value\Email`
    * `Kreait\Firebase\Value\Uid`
    * `Kreait\Firebase\Value\Url`

### Removed
* Removed local phone number validation when `giggsey/libphonenumber-for-php` was installed. Phone numbers are
  validated by the Firebase Service in any case, and even when a phone number was considered valid, in rare
  cases the Firebase API rejected them still.
* Removed support for the `FIREBASE_CREDENTIALS` environment variable to be used for credential discovery. 
  `GOOGLE_APPLICATION_CREDENTIALS` was already supported and is the same environment variable the official
  Google Libraries use as well.
* Dropped support for Guzzle 6.x
* All components have been made `final` and marked as `@internal`, if you're type-hinting dependencies in your
  application code, make sure you type-hint the `Kreait\Firebase\Contract\*` **interfaces**, not the
  `Kreait\Firebase\*` **implementations**
* Removed deprecated methods
  * `Auth::setCustomUserAttributes()`, use `Auth::setCustomUserClaims()` instead
  * `Auth::deleteCustomUserAttributes()`, use `Auth::setCustomUserClaims()` with null values instead
  * `Auth\UserRecord::$customAttributes`, use `Auth\UserRecord::$customClaims` instead
  * `Factory::withEnabledDebug()`, use `Factory::withHttpDebugLogger()` instead
* Removed deprecated/obsolete/internal classes and methods
  * `Kreait\Firebase\Project\ProjectId`
  * `Kreait\Firebase\Value\Provider`
  * `Kreait\Firebase\Project\TenantId`

[Unreleased]: https://github.com/kreait/firebase-php/compare/5.x...6.x
