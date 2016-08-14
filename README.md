# Firebase PHP SDK

[![Latest Stable Version](https://poser.pugx.org/kreait/firebase-php/v/stable)](https://packagist.org/packages/kreait/firebase-php)
[![Total Downloads](https://poser.pugx.org/kreait/firebase-php/downloads)](https://packagist.org/packages/kreait/firebase-php)
[![Latest Unstable Version](https://poser.pugx.org/kreait/firebase-php/v/unstable)](https://packagist.org/packages/kreait/firebase-php)
[![Build Status](https://travis-ci.org/kreait/firebase-php.svg?branch=master)](https://travis-ci.org/kreait/firebase-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master)

This SDK provides a fluent interface to your [Google Firebase](https://firebase.google.com) Application.

It supports Firebase V3 (authentication with a Google Service account) as
well as V2 (authentication with a database secret) and removes some limitations of the 
REST API, e.g. correct ordering of returned results.

Starting with version 2.0, this SDK requires PHP 7 - for PHP 5.5/5.6 support, please use
[Version 1.x](http://firebase-php.readthedocs.io/en/1.x/).

## Quickstart

Create a service account as 
[described in the Firebase Docs](https://firebase.google.com/docs/server/setup#add_firebase_to_your_app)
and download the service account JSON file, 
or retrieve a database secret from your Firebase application's project settings page.

```php
$firebase = Firebase::fromServiceAccount(__DIR__.'/google-service-account.json');
// or
$firebase = Firebase::fromDatabaseUriAndSecret(
    'https://<project>.firebaseio.com',
    '<database secret>'
);

$db = $firebase->getDatabase();

$fullTree = $db
    ->getReference('/')
    ->orderByKey(SORT_DESC)
    ->getValue(); // Shortcut for ->getSnapshot()->getValue()

print_r($fullTree);
```


## Documentation

The documentation is not up to date - the SDK will stay in beta until the docs at
http://firebase-php.readthedocs.io are complete. 

Please feel free to open an issue in this repository if something is unclear - but
if your IDE supports autocompletion, you should be fine :).

## Planned features

- Integration of the [Firebase Storage](https://firebase.google.com/docs/storage/)
- Automatic updates of [Firebase Rules](https://firebase.google.com/docs/database/security/) 
- Support for PHP Object Serialization/Deserialization

