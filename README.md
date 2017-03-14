# Firebase Admin SDK for PHP

[![Latest Stable Version](https://poser.pugx.org/kreait/firebase-php/v/stable)](https://packagist.org/packages/kreait/firebase-php)
[![Total Downloads](https://poser.pugx.org/kreait/firebase-php/downloads)](https://packagist.org/packages/kreait/firebase-php)
[![Build Status](https://travis-ci.org/kreait/firebase-php.svg?branch=master)](https://travis-ci.org/kreait/firebase-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master)

This SDK makes it easy to interact with [Google Firebase](https://firebase.google.com>)
applications.
 
Starting with version 2.0, this SDK requires PHP 7 - for PHP 5.5/5.6 support, please use
[Version 1.x](https://github.com/kreait/firebase-php/tree/1.x).

For support, please use the [issue tracker](https://github.com/kreait/firebase-php/issues/),
or join the Firebase Community Slack at https://firebase-community.appspot.com and join the #php channel.

- [Documentation](#documentation)
- [Usage example](#usage-example)
 
## Documentation

You can find the documentation at http://firebase-php.readthedocs.io/

- [Requirements](http://firebase-php.readthedocs.io/en/latest/overview.html#requirements)
- [Installation](http://firebase-php.readthedocs.io/en/latest/overview.html#installation)
- [Authentication](http://firebase-php.readthedocs.io/en/latest/authentication.html)
- [Working with the Realtime Database](http://firebase-php.readthedocs.io/en/latest/realtime-database.html)

- [Roadmap](http://firebase-php.readthedocs.io/en/latest/overview.html#roadmap)

## Usage example

```php
$firebase = (new \Firebase\Factory())
    ->withCredentials(__DIR__.'/path/to/google-service-account.json')
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
