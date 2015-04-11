# Configuring the Firebase client

The available configuration is defined in the [`ConfigurationInterface`](../src/ConfigurationInterface.php) and a default implementation can be found in [`Configuration`](../src/Configuration.php).

You can change the Firebase Client configuration bei instantiating your own Configuration object and passing it to the `Firebase` class through the constructor or with `Firebase::setConfiguration()`:


```php
use Kreait\Firebase\Configuration;
use Kreait\Firebase\Firebase;

$config = new Configuration();

$firebase = new Firebase('https://myapp.firebaseio.com', $config);
// or
$firebase = new Firebase('https://myapp.firebaseio.com');
$firebase->setConfiguration($config);
```

## Logger

```php
$logger = new Logger('firebase');
$logger->pushHandler(new StreamHandler('php://stdout'));

$config = new Configuration();
$config->setLogger($logger);
$config->getLogger();

$firebase->setConfiguration($config);
```

## HTTP Adapter

```php
use Ivory\HttpAdapter\FopenHttpAdapter;

$http = new FopenHttpAdapter();

$config = new Configuration();
$config->setHttpAdapter($http);
$config->getHttpAdapter();

$firebase->setConfiguration($config);
```

## Secret

The Firebase secret is needed to generate Authentication Tokens.

Default: undefined

```php
$config = new Configuration();
$config->setFirebaseSecret('xxxxx');
$config->getFirebaseSecret();

$firebase->setConfiguration($config);
```

## AuthTokenGenerator

The Authentication Token Generator is used to create authentication tokens to access a Firebase app. Normally, you
don't have to override it yourself as the Firebase PHP client already comes with one.

```php
// Must implement Kreait\Firebase\Auth\TokenGeneratorInterface
$myGenerator = new My\Custom\AuthTokenGenerator;

$config = new Configuration();
$config->setAuthTokenGenerator($myGenerator);
$config->getAuthTokenGenerator();

$firebase->setConfiguration($config);
```
