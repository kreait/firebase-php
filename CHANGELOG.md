# CHANGELOG

## The future of the Firebase Admin PHP SDK

Please read about the future of the Firebase Admin PHP SDK on the
[SDK's GitHub Repository](https://github.com/kreait/firebase-php).

## [Unreleased]

### Fixed

* Fix `WebPushNotification` Shape
  ([#895](https://github.com/kreait/firebase-php/pull/895))
* Catch `Throwable` and let the exception converter handle details
  ([#896](https://github.com/kreait/firebase-php/pull/896))

## [7.11.0] - 2024-05-16

### Added

* It is now possible to get a Remote Config template by its version number.
  ([#890](https://github.com/kreait/firebase-php/pull/890))

## [7.10.0] - 2024-04-25

### Changed

* FCM Messages are now sent asynchronously using HTTP connection pooling with HTTP/2. This should improve performance 
  when sending messages to many devices. 
  ([#874](https://github.com/kreait/firebase-php/pull/874))

## [7.9.1] - 2023-12-04

### Changed

* Re-enabled the use of `psr/http-message` v1.0
  ([#850](https://github.com/kreait/firebase-php/issues/850))

## [7.9.0] - 2023-11-30

### Added

* Added support for PHP 8.3

## [7.8.0] - 2023-11-25

### Added

* Added `Kreait\Firebase\Factory::withFirestoreClientConfig()` to support setting additional options when 
  creating the Firestore component.
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-firestore.html#add-firestore-configuration-options))
* Added `Kreait\Firebase\Factory::withFirestoreDatabase()` to specify the database used when creating the Firestore 
  component.
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/cloud-firestore.html#use-another-firestore-database))

## [7.7.0] - 2023-11-25

### Changed

* Required transitive dependencies directly ([#842](https://github.com/kreait/firebase-php/issues/842))
```json5
{
  "require": {
    // ...
    "ext-filter": "*",
    "guzzlehttp/promises": "^2.0",
    "guzzlehttp/psr7": "^2.6",
    "psr/clock": "^1.0",
    "psr/http-client": "^1.0",
    "psr/http-factory": "^1.0",
    "psr/http-message": "^2.0",
  }
}
```


## [7.6.0] - 2023-09-07

### Added

* The `Kreait\Firebase\Exception\Messaging\NotFound` exception now exposes the token that hasn't been found 
  with the `token()` method.
  ([#825](https://github.com/kreait/firebase-php/issues/825))

## [7.5.2] - 2023-06-29

### Added

* Added FCM error handling to the documentation

## [7.5.1] - 2023-06-29

### Fixed

* The cached KeySet used by the AppCheck component didn't use the same Guzzle Config Options as the other clients
  ([#812](https://github.com/kreait/firebase-php/issues/812))

## [7.5.0] - 2023-06-27

### Changed

* Replaced calls to deprecated FCM batch endpoints with asynchronous requests
  to the HTTP V1 API
  ([#804](https://github.com/kreait/firebase-php/pull/804)/[#805](https://github.com/kreait/firebase-php/pull/805))
* Removed message limit when sending multiple FCM messages
  * The message limit was needed when using the FCM batch endpoints because they used multipart requests and responses.
    The limit prevented these messages to become too large. Since we're now using asynchronous calls to send one
    request per message, this limitation is not needed anymore.
* Simplified convoluted Dynamic Link operations
  ([#810](https://github.com/kreait/firebase-php/pull/810))

### Removed

* Removed obsolete internal classes
  * `Kreait\Firebase\Http\HasSubRequests`
  * `Kreait\Firebase\Http\HasSubResponses`
  * `Kreait\Firebase\Http\Requests`
  * `Kreait\Firebase\Http\RequestWithSubRequests`
  * `Kreait\Firebase\Http\Responses`
  * `Kreait\Firebase\Http\ResponseWithSubResponses`
  * `Kreait\Firebase\Http\WrappedPsr7Response`
  * `Kreait\Firebase\Http\WrappedPsr7Request`
  * `Kreait\Firebase\Messaging\Http\Request\MessageRequest`
  * `Kreait\Firebase\Messaging\Http\Request\SendMessage`
  * `Kreait\Firebase\Messaging\Http\Request\SendMessageToTokens`
  * `Kreait\Firebase\Messaging\Http\Request\SendMessages`

* Removed obsolete internal methods
  * `Kreait\Firebase\Http\Middleware::responseWithSubResponses()`

* Removed obsolete Composer dependency `riverline/multipart-parser`

## [7.4.0] - 2023-06-18

### Added

* Added support for [Parameter Value Types](https://firebase.google.com/docs/reference/remote-config/rest/v1/RemoteConfig#parametervaluetype)
  when getting and setting a RemoteConfig template.
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/remote-config.html#parameter-value-types))

### Deprecated

* `Kreait\Firebase\RemoteConfig\ExplicitValue` is deprecated
* `Kreait\Firebase\RemoteConfig\DefaultValue` should be regarded as deprecated, it is kept to not create a breaking changes

## [7.3.1] - 2023-06-10

### Changed

* Removed direct dependency to `psr/http-message`

## [7.3.0] - 2023-06-03

### Added

* It is now possible to add config options and middlewares to the Guzzle HTTP Client performing the HTTP Requests
  to the Firebase APIs through the `HttpClientOptions` class.
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/setup.html#http-client-options))

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

https://github.com/kreait/firebase-php/blob/6.9.6/CHANGELOG.md

[Unreleased]: https://github.com/kreait/firebase-php/compare/7.11.0...7.x
[7.11.0]: https://github.com/kreait/firebase-php/compare/7.10.0...7.11.0
[7.10.0]: https://github.com/kreait/firebase-php/compare/7.9.1...7.10.0
[7.9.1]: https://github.com/kreait/firebase-php/compare/7.9.0...7.9.1
[7.9.0]: https://github.com/kreait/firebase-php/compare/7.8.0...7.9.0
[7.8.0]: https://github.com/kreait/firebase-php/compare/7.7.0...7.8.0
[7.7.0]: https://github.com/kreait/firebase-php/compare/7.6.0...7.7.0
[7.6.0]: https://github.com/kreait/firebase-php/compare/7.5.2...7.6.0
[7.5.2]: https://github.com/kreait/firebase-php/compare/7.5.1...7.5.2
[7.5.1]: https://github.com/kreait/firebase-php/compare/7.5.0...7.5.1
[7.5.0]: https://github.com/kreait/firebase-php/compare/7.3.1...7.5.0
[7.4.0]: https://github.com/kreait/firebase-php/compare/7.3.1...7.4.0
[7.3.1]: https://github.com/kreait/firebase-php/compare/7.3.0...7.3.1
[7.3.0]: https://github.com/kreait/firebase-php/compare/7.2.1...7.3.0
[7.2.1]: https://github.com/kreait/firebase-php/compare/7.2.0...7.2.1
[7.2.0]: https://github.com/kreait/firebase-php/compare/7.1.0...7.2.0
[7.1.0]: https://github.com/kreait/firebase-php/compare/7.0.3...7.1.0
[7.0.3]: https://github.com/kreait/firebase-php/compare/7.0.2...7.0.3
[7.0.2]: https://github.com/kreait/firebase-php/compare/7.0.1...7.0.2
[7.0.1]: https://github.com/kreait/firebase-php/compare/7.0.0...7.0.1
[7.0.0]: https://github.com/kreait/firebase-php/releases/tag/7.0.0
