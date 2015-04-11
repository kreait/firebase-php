# Authenticating requests to a Firebase app

To authenticate the requests to a Firebase app, you have to provide your firebase secret:

```php
use Kreait\Firebase\Configuration;
use Kreait\Firebase\Firebase;

$config = new Configuration();
$config->setFirebaseSecret('xxx');

$firebase = new Firebase('https://myapp.firebaseio.com', $config);
```

If you then want to start authenticating requests, you can do it by generating tokens:
 
```php
use Kreait\Firebase\Configuration;
use Kreait\Firebase\Firebase;

$config = new Configuration();
$config->setFirebaseSecret('xxx');

$firebase = new Firebase('https://myapp.firebaseio.com', $config);

$tokenGenerator = $firebase->getConfiguration()->getAuthTokenGenerator();

$customToken    = $tokenGenerator->createToken('12345', 'custom');
$anonymousToken = $tokenGenerator->createAnonymousToken();
$adminToken     = $tokenGenerator->createAdminToken();


$firebase->setAuthToken($adminToken);

// Perform some authenticated requests

$firebase->removeAuthToken();
```
