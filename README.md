# Firebase PHP Client

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/kreait/firebase-php?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Latest Stable Version](https://poser.pugx.org/kreait/firebase-php/v/stable.png)](https://packagist.org/packages/kreait/firebase-php)
[![Latest Unstable Version](https://poser.pugx.org/kreait/firebase-php/v/unstable.svg)](//packagist.org/packages/leaphly/cart-bundle)
[![Build Status](https://secure.travis-ci.org/kreait/firebase-php.png?branch=master)](http://travis-ci.org/kreait/firebase-php)
[![License](https://poser.pugx.org/kreait/firebase-php/license.svg)](https://packagist.org/packages/leaphly/cart-bundle)

A PHP client library for [http://www.firebase.com](http://www.firebase.com).

##Installation

The recommended way to install Firebase is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version:

```bash
composer require kreait/firebase-php
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

## Usage

```php
use Kreait\Firebase\Firebase;
use Kreait\Firebase\Reference;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require __DIR__.'/vendor/autoload.php';

$logger = new Logger('firebase');
$logger->pushHandler(new StreamHandler('php://stdout'));

$firebase = new Firebase('https://brilliant-torch-1474.firebaseio.com');
$firebase->setLogger($logger);

$firebase->set(["key" => "value"], 'path/to/my/location');
$firebase->update(["key" => "new value"], 'path/to/my/location');

$ref = new Reference($firebase, 'path/to/my/location');
for ($i = 1; $i <= 5; $i++) {
    $ref->push(['key' . $i => 'value' . $i]);
    // alternative: $firebase->push(['key' . $i => 'value' . $i], 'path/to/my/location');
}

$allData = $ref->get();

$firebase->delete('path/to/my/location');
```

### Output

```bash
[2015-01-09 12:42:37] firebase.DEBUG: PUT request to https://brilliant-torch-1474.firebaseio.com/path/to/my/location.json {"data_sent":{"key":"value"}} []
[2015-01-09 12:42:38] firebase.DEBUG: PATCH request to https://brilliant-torch-1474.firebaseio.com/path/to/my/location.json {"data_sent":{"key":"new value"}} []
[2015-01-09 12:42:39] firebase.DEBUG: POST request to https://brilliant-torch-1474.firebaseio.com/path/to/my/location.json {"data_sent":{"key1":"value1"}} []
[2015-01-09 12:42:39] firebase.DEBUG: POST request to https://brilliant-torch-1474.firebaseio.com/path/to/my/location.json {"data_sent":{"key2":"value2"}} []
[2015-01-09 12:42:40] firebase.DEBUG: POST request to https://brilliant-torch-1474.firebaseio.com/path/to/my/location.json {"data_sent":{"key3":"value3"}} []
[2015-01-09 12:42:40] firebase.DEBUG: POST request to https://brilliant-torch-1474.firebaseio.com/path/to/my/location.json {"data_sent":{"key4":"value4"}} []
[2015-01-09 12:42:41] firebase.DEBUG: POST request to https://brilliant-torch-1474.firebaseio.com/path/to/my/location.json {"data_sent":{"key5":"value5"}} []
[2015-01-09 12:42:42] firebase.DEBUG: GET request to https://brilliant-torch-1474.firebaseio.com/path/to/my/location.json {"data_sent":null} []
[2015-01-09 12:42:42] firebase.DEBUG: DELETE request to https://brilliant-torch-1474.firebaseio.com/path/to/my/location.json {"data_sent":null} []
```

### Development Notes (in Progress)

- [chag](https://github.com/mtdowling/chag) for the changelog
- [PHP Coding Standards Fixer](http://cs.sensiolabs.org) before commiting code
- Present tense in commit messages
- Pull Requests should be rebased into one commit before merging

