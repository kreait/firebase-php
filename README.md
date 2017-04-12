# Firebase PHP Client

[![Latest Stable Version](https://poser.pugx.org/kreait/firebase-php/v/stable)](https://packagist.org/packages/kreait/firebase-php)

A PHP client for the [Google Firebase](https://firebase.google.com) Realtime Database

## Security fixes only

The 1.x branch of this library is supported for critical security issues only.
Releases are only made on an as-needed basis.

Please use the [latest stable version](https://github.com/kreait/firebase-php/releases/latest).

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

You can find the documentation at http://firebase-php.readthedocs.io/en/1.x/
