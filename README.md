# Firebase PHP Client

[![Packagist](https://img.shields.io/packagist/v/kreait/firebase-php.svg?style=flat-square)](https://packagist.org/packages/kreait/firebase-php)
[![Travis](https://img.shields.io/travis/kreait/firebase-php.svg?style=flat-square)](https://travis-ci.org/kreait/firebase-php)
[![Packagist](https://img.shields.io/packagist/l/kreait/firebase-php.svg?style=flat-square)](https://github.com/kreait/firebase-php/blob/master/LICENSE)
[![Gitter](https://img.shields.io/badge/Gitter-Join%20Chat-45cba1.svg?style=flat-square)](https://gitter.im/kreait/firebase-php)

A PHP client for [http://www.firebase.com](http://www.firebase.com).

---

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

Until the next version of the HTTP Adapter [with baseUrl support](https://github.com/egeloen/ivory-http-adapter/pull/52) is released, you also have to require the current development version of that:

```bash
composer require egeloen/http-adapter ~0.6@dev
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

## Usage

### Basic commands

```php
use Kreait\Firebase\Firebase;

$firebase = new Firebase('https://brilliant-torch-1474.firebaseio.com');

$firebase->set(['name' => 'John Doe', 'email' => 'john@doh.com'], 'data/users/john');
$firebase->update(['email' => 'john@doe.com'], 'data/users/john');
$firebase->push(['name' => 'Jane Doe', 'email' => 'jane@doe.com'], 'data/users');
$firebase->delete('data/users/john');
$firebase->get('data/users');
$firebase->get('data/users', ['shallow' => true]); // Limit the depth of the data received

```

### References

A reference is a shortcut to a subtree of your Firebase data. You can use the same methods as with a `Firebase` object, with the addition of being able to omit the location parameter when performing a `push` or a `delete`.

```php
use Kreait\Firebase\Firebase;
use Kreait\Firebase\Reference;

$firebase = new Firebase('https://brilliant-torch-1474.firebaseio.com');

$users = new Reference($firebase, 'data/users');
$users->set(['name' => 'Jack Doe', 'email' => 'jack@doh.com'], 'jack');
$users->update(['email' => 'jack@doe.com'], 'jack');
$users->push(['name' => 'Jane Doe', 'email' => 'jane@doe.com']);
$users->delete('jack');
$users->delete();
```

### Use your own HTTP client

The Firebase client uses the [HTTP Adapter](https://github.com/egeloen/ivory-http-adapter) by Eric Geloen which enables support for a multitude of HTTP clients. If you want to override the default HTTP Client (cURL) used by Firebase, you can use [one of the supported HTTP adapters](https://github.com/egeloen/ivory-http-adapter/blob/master/doc/adapters.md) and use it as the second parameter when creating a Firebase instance:

```php
use Ivory\HttpAdapter\FopenHttpAdapter;
use Kreait\Firebase\Firebase;

$http = new FopenHttpAdapter();
$firebase = new Firebase('https://brilliant-torch-1474.firebaseio.com', $http);
```

## Extended Example

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

$firebase->set(['name' => 'John Doe', 'email' => 'john@doh.com'], 'data/users/john');
$firebase->update(['email' => 'john@doe.com'], 'data/users/john');

$ref = new Reference($firebase, 'data/users');
for ($i = 1; $i <= 5; $i++) {
    $ref->push(['name' => 'Name ' . $i]);
}

$allUsers = $ref->get();

$firebase->delete('data/users/john');
```

#### Output

```bash
[2015-01-10 04:11:41] firebase.DEBUG: PUT request to https://brilliant-torch-1474.firebaseio.com/data/users/john.json {"data_sent":{"name":"John Doe","email":"john@doh.com"}} []
[2015-01-10 04:11:42] firebase.DEBUG: PATCH request to https://brilliant-torch-1474.firebaseio.com/data/users/john.json {"data_sent":{"email":"john@doe.com"}} []
[2015-01-10 04:11:43] firebase.DEBUG: POST request to https://brilliant-torch-1474.firebaseio.com/data/users.json {"data_sent":{"name":"Name 1"}} []
[2015-01-10 04:11:43] firebase.DEBUG: POST request to https://brilliant-torch-1474.firebaseio.com/data/users.json {"data_sent":{"name":"Name 2"}} []
[2015-01-10 04:11:44] firebase.DEBUG: POST request to https://brilliant-torch-1474.firebaseio.com/data/users.json {"data_sent":{"name":"Name 3"}} []
[2015-01-10 04:11:44] firebase.DEBUG: POST request to https://brilliant-torch-1474.firebaseio.com/data/users.json {"data_sent":{"name":"Name 4"}} []
[2015-01-10 04:11:45] firebase.DEBUG: POST request to https://brilliant-torch-1474.firebaseio.com/data/users.json {"data_sent":{"name":"Name 5"}} []
[2015-01-10 04:11:46] firebase.DEBUG: GET request to https://brilliant-torch-1474.firebaseio.com/data/users.json [] []
[2015-01-10 04:11:46] firebase.DEBUG: DELETE request to https://brilliant-torch-1474.firebaseio.com/data/users/john.json [] []
```


## Development Notes (in Progress)

- [chag](https://github.com/mtdowling/chag) for the changelog
- [PHP Coding Standards Fixer](http://cs.sensiolabs.org) before commiting code
- Present tense in commit messages
- Pull Requests should be rebased into one commit before merging

