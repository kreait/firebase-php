# CHANGELOG

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
