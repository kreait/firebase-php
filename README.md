# Firebase PHP Client

[![Latest Stable Version](https://poser.pugx.org/kreait/firebase-php/version)](https://packagist.org/packages/kreait/firebase-php)
[![Build Status](https://travis-ci.org/kreait/firebase-php.svg?branch=master)](https://travis-ci.org/kreait/firebase-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master)
[![Gitter](https://img.shields.io/badge/Gitter-Join%20Chat-45cba1.svg)](https://gitter.im/kreait/firebase-php)

A PHP client for [http://www.firebase.com](http://www.firebase.com).

---

## Installation

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

## Documentation

1. [Working with the `Firebase` class](doc/firebase.md)
1. [Working with References](doc/reference.md)
1. [Querying data](doc/queries.md)
1. [Configuration](doc/configuration.md)
1. [Authentication](doc/authentication.md)

## Example

```php
require __DIR__.'/vendor/autoload.php';

use Kreait\Firebase\Configuration;
use Kreait\Firebase\Firebase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('firebase');
$logger->pushHandler(new StreamHandler('php://stdout'));

$configuration = new Configuration();
$configuration->setLogger($logger);

$firebase = new Firebase('https://myapp.firebaseio.com', $configuration);

$simpsons = $firebase->getReference('data/simpsons');

$homer = $simpsons->getReference('homer');
$homer->set(['name' => 'Homer Simpson', 'email' => 'marge@simpson.com']);
// Ooops, wrong email address
$homer->update(['email' => 'homer@simpson.com']);

$children = $homer->getReference('children');
$bart = $children->push(['name' => 'Bart Simpson', 'email' => 'bart@simpson.com']);
$lisa = $children->push(['name' => 'Lisa Simpson', 'email' => 'lisa@simpson.com']);
$maggie = $children->push(['name' => 'Maggie Simpson', 'email' => 'maggie@simpson.com']);

print_r($homer->getData());
```


## Development Notes (in Progress)

- [chag](https://github.com/mtdowling/chag) for the changelog
- [PHP Coding Standards Fixer](http://cs.sensiolabs.org) before commiting code
- Present tense in commit messages
- Pull Requests should be rebased into one commit before merging

