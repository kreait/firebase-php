# CHANGELOG

## [Unreleased]
### Removed
* Dropped support for Guzzle 6.x
* Removed deprecated methods
  * `Auth::setCustomUserAttributes()`, use `Auth::setCustomUserClaims()` instead
  * `Auth::deleteCustomUserAttributes()`, use `Auth::setCustomUserClaims()` with null values instead
  * `Auth\UserRecord::$customAttributes`, use `Auth\UserRecord::$customClaims` instead
  * `Factory::withEnabledDebug()`, use `Factory::withHttpDebugLogger()` instead
* Removed deprecated/obsolete internal classes and methods)
  * `Kreait\Firebase\Value\Provider`

[Unreleased]: https://github.com/kreait/firebase-php/compare/5.x...6.x
