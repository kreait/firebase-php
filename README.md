# Firebase PHP Client

[![Latest Stable Version](https://poser.pugx.org/kreait/firebase-php/v/stable.png)](https://packagist.org/packages/kreait/firebase-php)
[![Latest Unstable Version](https://poser.pugx.org/kreait/firebase-php/v/unstable.svg)](//packagist.org/packages/leaphly/cart-bundle)
[![Build Status](https://secure.travis-ci.org/kreait/firebase-php.png?branch=master)](http://travis-ci.org/kreait/firebase-php)
[![License](https://poser.pugx.org/kreait/firebase-php/license.svg)](https://packagist.org/packages/leaphly/cart-bundle)

A PHP client library for [http://www.firebase.com](http://www.firebase.com).

### Installation

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

### Usage

```php
use Kreait\Firebase\Firebase;
use Kreait\Firebase\Reference;

$firebase = new Firebase('https://my-application-1234.firebaseio.com');

$allData = $firebase->get();

for ($i = 1; $i <= 5; $i++) {
    $firebase->set()
}