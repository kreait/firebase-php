# Firebase PHP SDK

[![Latest Stable Version](https://poser.pugx.org/kreait/firebase-php/v/stable)](https://packagist.org/packages/kreait/firebase-php)
[![Total Downloads](https://poser.pugx.org/kreait/firebase-php/downloads)](https://packagist.org/packages/kreait/firebase-php)
[![Latest Unstable Version](https://poser.pugx.org/kreait/firebase-php/v/unstable)](https://packagist.org/packages/kreait/firebase-php)
[![Build Status](https://travis-ci.org/kreait/firebase-php.svg?branch=master)](https://travis-ci.org/kreait/firebase-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master)

This SDK makes it easy to interact with [Google Firebase](https://firebase.google.com>)
applications.

- Simple and fluent interface to work with References, Querys and Data snapshots
- Abstracts away the underlying communication with the Firebase REST API
- Supports authentication with a Google service account (V3) or a database secret (V2)
- Removes limitations of the REST API (e.g.
  [sorted results](https://firebase.google.com/docs/database/rest/retrieve-data#section-rest-ordered-data))
 
Starting with version 2.0, this SDK requires PHP 7 - for PHP 5.5/5.6 support, please use
[Version 1.x](http://firebase-php.readthedocs.io/en/1.x/).

# Installation

The recommended way to install the Firebase SDK is with [Composer](http://getcomposer.org).
Composer is a dependency management tool for PHP that allows you to declare the dependencies your project needs and
installs them into your project.

```bash
# Install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

You can add the Firebase SDK as a dependency using the composer.phar CLI:

```bash
php composer.phar require kreait/firebase-php ^2.0@beta
```

*The ``@beta`` version constraint is only needed until the documentation is completed.*

Alternatively, you can specify the Firebase SDK as a dependency in your project's existing composer.json file:

```json
{
  "require": {
     "kreait/firebase-php": "^2.0@beta"
  }
}
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

You can find out more on how to install Composer, configure autoloading, and
other best-practices for defining dependencies at [getcomposer.org](http://getcomposer.org).

## Quickstart

Create a service account as 
[described in the Firebase Docs](https://firebase.google.com/docs/server/setup#add_firebase_to_your_app)
and download the service account JSON file, or retrieve a database secret from your Firebase application's 
project settings page.

```php
$firebase = Firebase::fromServiceAccount(__DIR__.'/google-service-account.json');

$database = $firebase->getDatabase();

$root = $database->getReference('/');

$completeSnapshot = $root->getSnapshot();

$root->getChild('users')->push([
    'username' => uniqid('user', true),
    'email' => uniqid('email', true).'@domain.tld'
]);

$users = $database->getReference('users');

$sortedUsers = $users
    ->orderByChild('username', SORT_DESC)
    ->limitToFirst(10)
    ->getValue(); // shortcut for ->getSnapshot()->getValue()

$users->remove();
```

## Documentation

The documentation is not complete yet - the SDK will stay in beta until the docs at
http://firebase-php.readthedocs.io are finished. 

Please feel free to open an issue in this repository if something is unclear - but
if your IDE supports autocompletion, you should be fine :).

## Roadmap

- Integration of the [Firebase Storage](https://firebase.google.com/docs/storage/)
- Automatic updates of [Firebase Rules](https://firebase.google.com/docs/database/security/) 
- Support for PHP Object Serialization/Deserialization
- Listening to the [Firebase event stream](https://firebase.google.com/docs/reference/rest/database/#section-streaming)
