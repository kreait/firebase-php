# Firebase PHP Client

[![Latest Stable Version](https://poser.pugx.org/kreait/firebase-php/version)](https://packagist.org/packages/kreait/firebase-php)
[![Build Status](https://travis-ci.org/kreait/firebase-php.svg?branch=1.x)](https://travis-ci.org/kreait/firebase-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=1.x)

A PHP client for the [Google Firebase](https://firebase.google.com) Realtime Database

---

## Quick usage example

```php
use Kreait\Firebase\Configuration;
use Kreait\Firebase\Firebase;

$config = new Configuration();
$config->setAuthConfigFile('/path/to/google-service-account.json');

$firebase = new Firebase('https://my-app.firebaseio.com', $config);

$firebase->set(['key' => 'value'], 'my/data');

print_r($firebase->get('my/data'));

$firebase->delete('my/data');
```

## Documentation

You can find the documentation at http://firebase-php.readthedocs.io
