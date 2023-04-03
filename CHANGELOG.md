# CHANGELOG

## [Unreleased]

## [7.2.1] - 2023-04-04

### Fixed

* Fixed a user's MFA information not being correctly parsed
  ([#783](https://github.com/kreait/firebase-php/pull/783))

## [7.2.0] - 2023-03-24

### Added

* Added support for the Firebase Auth Emulator when using `lcobucci/jwt` 5.*

## [7.1.0] - 2023-03-01

### Added

* Added support for `lcobucci/jwt` 5.*

## [7.0.3] - 2023-02-13

### Fixed

* Restored support for using a JSON string in the `GOOGLE_APPLICATION_CREDENTIALS` environment variable.
  ([#767](https://github.com/kreait/firebase-php/pull/767))

## [7.0.2] - 2023-01-27

### Fixed

* Cloud Messaging: The APNS `content-available` payload field was not set correctly when a message contained
  message data at the root level, but not at the APNS config level.
  ([#762](https://github.com/kreait/firebase-php/pull/762))

## [7.0.1] - 2023-01-24

### Fixed

* When trying to work with unknown FCM tokens, errors returned from the Messaging REST API were not passed to
  the `NotFound` exception, which prevented the inspection of further details.
  ([#760](https://github.com/kreait/firebase-php/pull/760))

## [7.0.0] - 2022-12-20

The most notable change is that you need PHP 8.1/8.2 to use the new version. The language migration of
the SDK introduces breaking changes concerning the strictness of parameter types almost everywhere in
the SDK - however, this should not affect your project in most cases (unless you have used internal classes
directly or by extension).

This release adds many more PHPDoc annotations to support the usage of Static Analysis Tools like PHPStan
and Psalm and moves away from doing runtime checks. It is strongly recommended to use a Static Analysis
Tool and ensure that input values are validated before handing them over to the SDK.

### Added features

* Added support for verifying Firebase App Check Tokens. ([#747](https://github.com/kreait/firebase-php/pull/747))

### Notable changes

* The ability to disable credentials auto-discovery has been removed. If you don't want a service account to be
  auto-discovered, provide it by using the `withServiceAccount()` method of the Factory or by setting the
  `GOOGLE_APPLICATION_CREDENTIALS` environment variable. Depending on the environment in which the SDK is running,
  credentials could be auto-discovered otherwise, for example on GCP or GCE.

See **[UPGRADE-7.0](UPGRADE-7.0.md) for more details on the changes between 6.x and 7.0.**

## 6.x Changelog

https://github.com/kreait/firebase-php/blob/6.x/CHANGELOG.md

[Unreleased]: https://github.com/kreait/firebase-php/compare/7.2.1...7.x
[7.2.1]: https://github.com/kreait/firebase-php/compare/7.2.0...7.2.1
[7.2.0]: https://github.com/kreait/firebase-php/compare/7.1.0...7.2.0
[7.1.0]: https://github.com/kreait/firebase-php/compare/7.0.3...7.1.0
[7.0.3]: https://github.com/kreait/firebase-php/compare/7.0.2...7.0.3
[7.0.2]: https://github.com/kreait/firebase-php/compare/7.0.1...7.0.2
[7.0.1]: https://github.com/kreait/firebase-php/compare/7.0.0...7.0.1
[7.0.0]: https://github.com/kreait/firebase-php/releases/tag/7.0.0
