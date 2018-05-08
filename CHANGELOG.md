# CHANGELOG

## 4.7.0 - 2018-05-08

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
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/storage.html))

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
