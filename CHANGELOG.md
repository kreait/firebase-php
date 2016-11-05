# CHANGELOG

## Unreleased

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

## 1.1 - 2016-07-20

* Adds support for "equalTo" queries

## 1.0.1 - 2016-07-16

* Enables the usage of egeleon/http-adapter ^1.0 in addition to ^0.8.

## 1.0 - 2016-07-15

* Removed deprecated methods and revisited the code and documentation.

## 0.10.2 - 2016-07-15

* Added support for authentication overrides when using a Google Service account
* It is now easier to override authentication credentials through
  `$firebase->setAuthOverride($uid, array $claims = [])`.
  * There's no need to create and set the needed custom tokens yourself.
  * The new method works regardless of whether you use a database secret or
    a Google Service Account for the basic authentication.
* Updated authentication docs
* Added methods:
  * `Firebase::setAuthOverride(string $uid, array $claims = [])`
  * `Firebase::removeAuthOverride()`
* Deprecated methods:
  * `Firebase::setAuthToken()`
  * `Firebase::getAuthToken()`
  * `Firebase::hasAuthToken()`
  * `Firebase::removeAuthToken()`

## 0.10.1 - 2016-07-03

* Added support for authentication with a Google Service account

## 0.10.0 - 2016-04-28

* Added magic getters, allowing to write `$firebase->foo` instead of instead of `$firebase->getReference('foo')`
* Deprecated magic callers to allow writing `$firebase->foo()`

## 0.9.1 - 2016-03-18

* Added magic reference methods, to allow writing `$firebase->foo()`  
  instead of `$firebase->getReference('foo')`

## 0.9 - 2015-12-07

* Added support for shallow queries (thanks to [@famersbs](https://github.com/famersbs) for the initial 
  implementation in [#17](https://github.com/kreait/firebase-php/pull/17))

## 0.8 - 2015-12-07

* Allow usage of phpunit/phpunit ^5.0
* Allow usage of symfony/event-dispatcher ^3.0
* Be more specific about PHP versions (^5.5|^7.0)

## 0.7 - 2015-08-13

* Update Ivory HTTP Adapter and Tape Recorder Subscriber to current versions.

## 0.6.2 - 2015-05-04

* Guess best HTTP adapter instead of using fixed CurlHttpAdapter.

## 0.6.1 - 2015-04-17

* Fixed a bug where `Reference::update()` would throw an error with multidimensional data.

## 0.6 - 2015-04-12

* Added Authentication support
* Ensure PHP7 and HHVM support
* Improved tests and general code quality

## 0.5 - 2015-04-02

* Use `Configuration` object instead of configuring `Firebase` directly.

## 0.4.2 - 2015-03-25

* Fix Query documentation
* Use Tape Recorder Subscriber for Reference Tests
* Make sure data returned from Firebase is an array (null responses weren't handled correctly)

## 0.4.1 - 2015-02-23

* Use Tape Recorder Subscriber as distinct package instead of relying on the PR branch of the Ivory HTTP Adapter.

## 0.4.0 - 2015-02-10

* Use TapeRecorder subscriber in HTTP adapter to use fixtures instead of real HTTP requests.
* Use [dotenv](https://github.com/vlucas/phpdotenv/) for the test environment.
* Moved `FirebaseException` to own namespace.
* Add support for Queries.

## 0.3.1 - 2015-01-27

* Bugfix: Allow whitespaces in locations

## 0.3 - 2015-01-27

* **Breaking changes: References have changed behaviour**
* `Reference` doesn't extend `Firebase` anymore and has changed behaviour (see the [doc/02-references.md](documentation about References)).
* Reference data can be accessed with `$reference->getData()`, which returns an array, or directly with `$reference['key']`.
* Reference data can be updated with `$reference->update(['key' => 'value'])` or with `$reference['key'] = 'value'`.
* Improved test coverage.
* Added Makefile to ease test execution.
* Extended documentation and moved in to the doc folder.

## 0.2.4 - 2015-01-21

* Add `ext-mbstring` as a requirement in `composer.json`
* Improved test coverage and added badges to README to show it off :)

## 0.2.3 - 2015-01-20

Fixed an error where the throw of an exception would throw an exception because of a wrong usage of `sprintf()`

## 0.2.2 - 2015-01-20

Fixed a case where an exception would be thrown inside an exception when no response was present.

## 0.2.1 - 2015-01-19

The Firebase library now has its own base URL handling so that a stable version of the HTTP adapter can be used.

## 0.2 - 2015-01-13

* Better handling of server errors: Instead of using hard coded exception messages for assumed server errors, a single server error exception now includes the server's error message, if available.
* It is now possible to use the `shallow` parameter when performing a GET request. See [the Firebase Docs on Query Parameters](https://www.firebase.com/docs/rest/api/#section-query-parameters) for a detailed description.
* `Firebase::push()` now returns the generated key as a string (not as an array `['name' => '...']`) anymore

## 0.1.1 - 2015-01-09

* The logger output now is less verbose and includes full URLs
* The README now includes an usage example

## 0.1 - 2015-01-09

Initial release
