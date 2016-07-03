# Authenticating requests to a Firebase app

- [Firebase SDK 3.x)](#firebase-sdk-3x)
- [Firebase SDK 1.x/2.x)](#firebase-sdk-1x-2x)

## Firebase SDK 3.x

To create custom tokens with the Firebase server SDKs, you must have a service account. 
Follow the [server SDK setup instructions](https://firebase.google.com/docs/server/setup/)
for more information on how to initialize your server SDK with a service account.

Once you have downloaded the service account's key file, you can generate authenticate requests
to the Firebase app's database like this:

```php
use Kreait\Firebase\Configuration;
use Kreait\Firebase\Firebase;

$config = new Configuration();
$config->setAuthConfigFile('/path/to/google-service-account.json');

$firebase = new Firebase('https://myapp.firebaseio.com', $config);
```

## Firebase SDK 1.x/2.x

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
