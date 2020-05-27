# CHANGELOG

## 5.3.0 - 2020-05-27

* In addition to with `getenv()`, the SDK now looks for environment variables in
  `$_SERVER` and `$_ENV` as well. 

## 5.2.0 - 2020-05-03

* It is now possible to retrieve the Firebase User ID directly from a `SignInResult` after a successful user sign-in with `SignInResult::firebaseUserId()`

## 5.1.1 - 2020-04-16

* Custom Token Generation was not possible with an auto-discovered Service Account 
  ([#412](https://github.com/kreait/firebase-php/issues/412))

## 5.1.0 - 2020-04-06

* Fetched authentication tokens (to authenticate requests to the Firebase API) are now cached in-memory
  by default ([#404](https://github.com/kreait/firebase-php/issues/404))

## 5.0.0 - 2020-04-01

**If you are not using any classes or methods marked as `@deprecated` or `@internal` you should be able to upgrade from a 4.x release to 5.0 without changes to your code.**

* Dropped support for unsupported PHP versions. Supported PHP versions are `7.2`, `7.3` and `7.4`.
* Removed deprecated methods and classes.

## 4.x

For the 4.x changelogs, please visit

https://github.com/kreait/firebase-php/blob/4.x/CHANGELOG.md
