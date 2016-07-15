# Configuring the Firebase client

```php
use Kreait\Firebase\Configuration;
use Kreait\Firebase\Firebase;

$config = new Configuration();

$config->setLogger(/* Psr\Log\LoggerInterface */ $logger);
$config->setHttpAdapter(/* Ivory\HttpAdapter\HttpAdapterInterface */ $httpAdapter);
$config->setFirebaseSecret(/* string */ $databaseSecret);
$config->setAuthConfigFile(/* string */ $pathToGoogleServiceAccountFile);

$firebase = new Firebase('https://myapp.firebaseio.com', $config);
```

