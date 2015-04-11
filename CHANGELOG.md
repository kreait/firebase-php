# CHANGELOG

## 0.5 - 2015-04-02

* Use `Configuration` object instead of configuring `Firebase` directly.

## Unreleased

* Add Authentication support
* Ensure PHP7 and HHVM support

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
