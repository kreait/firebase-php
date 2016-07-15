# Firebase PHP Client

[![Latest Stable Version](https://poser.pugx.org/kreait/firebase-php/version)](https://packagist.org/packages/kreait/firebase-php)
[![Build Status](https://travis-ci.org/kreait/firebase-php.svg?branch=master)](https://travis-ci.org/kreait/firebase-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master)

A PHP client for the [Google Firebase](https://firebase.google.com) Realtime Database

---

## Quick usage example

```php
use Kreait\Firebase\Configuration;
use Kreait\Firebase\Firebase;

$config = new Configuration();
$config->setAuthConfigFile('/path/to/google-service-account.json');
// or
$config->setFirebaseSecret('my-firebase-secret');

$firebase = new Firebase('https://the-simpsons.firebaseio.com', $config);

$firebase->simpsons->set(['name' => 'The Simpsons', 'hometown' => 'Springfield']);

$family = $firebase->simpsons;

$family->marge->set(['name' => 'Marge', 'age' => 46]);
$family->homer->set(['name' => 'Homer', 'age' => 38]);

$family->children->push(['name' => 'Maggie']);
$family->children->push(['name' => 'Bart']);
$family->children->push(['name' => 'Maggie']);

$family->delete();
```

## Installation

The recommended way to install Firebase is through [Composer](http://getcomposer.org).

```bash
composer require kreait/firebase-php
```

## Documentation

1. [Working with the `Firebase` class](doc/firebase.md)
1. [Working with References](doc/reference.md)
1. [Querying data](doc/queries.md)
1. [Configuration](doc/configuration.md)
1. [Authentication](doc/authentication.md)
1. [Using this library with Symfony](doc/symfony.md)
