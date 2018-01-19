# Firebase Admin SDK for PHP

Interact with [Google Firebase](https://firebase.google.com) from your PHP application.

[![Current version](https://img.shields.io/packagist/v/kreait/firebase-php.svg)](https://packagist.org/packages/kreait/firebase-php)
[![Supported PHP version](https://img.shields.io/packagist/php-v/kreait/firebase-php.svg)]()
[![Build Status](https://travis-ci.org/kreait/firebase-php.svg?branch=master)](https://travis-ci.org/kreait/firebase-php)
[![GitHub license](https://img.shields.io/github/license/kreait/firebase-php.svg)](https://github.com/kreait/firebase-php/blob/master/LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/kreait/firebase-php.svg)]()
[![Maintainability](https://api.codeclimate.com/v1/badges/577e2f8f5df7f4133675/maintainability)](https://codeclimate.com/github/kreait/firebase-php/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/577e2f8f5df7f4133675/test_coverage)](https://codeclimate.com/github/kreait/firebase-php/test_coverage)

## Quickstart

Full documentation at [firebase-php.readthedocs.io](https://firebase-php.readthedocs.io/).

```bash
composer require kreait/firebase-php ^3.0
```

```php
<?php

require __DIR__.'/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

$serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/google-service-account.json');

$firebase = (new Factory)
    ->withServiceAccount($serviceAccount)
    ->withDatabaseUri('https://my-project.firebaseio.com')
    ->create();

$database = $firebase->getDatabase();

$newPost = $database
    ->getReference('blog/posts')
    ->push([
        'title' => 'Post title',
        'body' => 'Post body'
    ]);

$newPost->getChild('title')->set('Changed post title');

$newPost->remove();
```

For errors and missing features, please use the [issue tracker](https://github.com/kreait/firebase-php/issues/).

For general support, join the `#php` channel at [https://firebase.community/](https://firebase.community/).
