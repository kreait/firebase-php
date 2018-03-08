# Firebase Admin SDK for PHP

Interact with [Google Firebase](https://firebase.google.com) from your PHP application.

[![Current version](https://img.shields.io/packagist/v/kreait/firebase-php.svg)](https://packagist.org/packages/kreait/firebase-php)
[![Supported PHP version](https://img.shields.io/packagist/php-v/kreait/firebase-php.svg)]()
[![Build Status](https://travis-ci.org/kreait/firebase-php.svg?branch=master)](https://travis-ci.org/kreait/firebase-php)
[![GitHub license](https://img.shields.io/github/license/kreait/firebase-php.svg)](https://github.com/kreait/firebase-php/blob/master/LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/kreait/firebase-php.svg)]()
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master)

If you are interested in using the PHP Admin SDK as a client for end-user access (for example, in a web application), as opposed to admin access from a privileged environment (like a server), you should instead follow the [instructions for setting up the client JavaScript SDK](https://firebase.google.com/docs/web/setup).

## Documentation

You can find the full documentation at
[firebase-php.readthedocs.io](https://firebase-php.readthedocs.io/).

A ready-to-go repository with usage examples can be found at 
https://github.com/jeromegamez/firebase-php-examples

## Feature matrix

| Feature | PHP | Node.js | Java | Python | Go |
| --- | :---: | :---: | :---: | :---: | :---: |
| [Custom Token Minting](https://firebase.google.com/docs/auth/admin/create-custom-tokens) | ✅ | ✅ | ✅ | ✅ | ✅ |
| [ID Token Verification](https://firebase.google.com/docs/auth/admin/verify-id-tokens)	| ✅ | ✅ | ✅ | ✅ | ✅ |
| [Realtime Database API](https://firebase.google.com/docs/database/admin/start) | ✅* | ✅ | ✅ | ✅* | ✅ |
| [User Management API](https://firebase.google.com/docs/auth/admin/manage-users) | ✅ | ✅ | ✅ | ✅ | ✅ |
| [Cloud Messaging API](https://firebase.google.com/docs/cloud-messaging/admin/) |  | ✅ | ✅ | ✅ | ✅ |				
| [Cloud Storage API](https://firebase.google.com/docs/storage/admin/start) | ✅ | ✅ | ✅ | ✅ | ✅ |
| [Cloud Firestore API](https://firebase.google.com/docs/firestore/) | | ✅ | ✅ | ✅ | ✅ |

> Note: The Realtime Database API in PHP/Python Admin SDK currently does not support realtime event listeners. 
This means there is no provision for adding event listeners to a database reference in order to automatically 
receive realtime update notifications. Instead, in PHP/Python updates should be proactively fetched by explicitly 
invoking read operations.

### Usage example

You can find more usage examples in the
[tests directory](https://github.com/kreait/firebase-php/tree/master/tests).

```php
<?php

require __DIR__.'/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

// This assumes that you have placed the Firebase credentials in the same directory
// as this PHP file.
$serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/google-service-account.json');

$firebase = (new Factory)
    ->withServiceAccount($serviceAccount)
    // The following line is optional if the project id in your credentials file
    // is identical to the subdomain of your Firebase project. If you need it,
    // make sure to replace the URL with the URL of your project.
    ->withDatabaseUri('https://my-project.firebaseio.com')
    ->create();

$database = $firebase->getDatabase();

$newPost = $database
    ->getReference('blog/posts')
    ->push([
        'title' => 'Post title',
        'body' => 'This should probably be longer.'
    ]);

$newPost->getKey(); // => -KVr5eu8gcTv7_AHb-3-
$newPost->getUri(); // => https://my-project.firebaseio.com/blog/posts/-KVr5eu8gcTv7_AHb-3-

$newPost->getChild('title')->set('Changed post title');
$newPost->getValue(); // Fetches the data from the realtime database
$newPost->remove();
```

## Support

For errors and missing features, please use the [issue tracker](https://github.com/kreait/firebase-php/issues/).

For general support, join the `#php` channel at [https://firebase.community/](https://firebase.community/).
