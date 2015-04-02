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

Default: `Psr\Log\NullLogger`

```php
$logger = new Logger('firebase');
$logger->pushHandler(new StreamHandler('php://stdout'));

$config = new Configuration();
$config->setLogger($logger);
$config->getLogger();

$firebase->setConfiguration($config);
```

## HTTP Adapter

Default: `Ivory\HttpAdapter\CurlHttpAdapter`

```php
use Ivory\HttpAdapter\FopenHttpAdapter;

$http = new FopenHttpAdapter();

$config = new Configuration();
$config->setHttpAdapter($http);
$config->getHttpAdapter();

$firebase->setConfiguration($config);
```

## Secret

The Firebase secret can be used to generate Authentication Tokens or to authenticate requests to a Firebase app directly.

Default: undefined

```php
$config = new Configuration();
$config->setFirebaseSecret('xxxxx');
$config->getFirebaseSecret();

$firebase->setConfiguration($config);
```
