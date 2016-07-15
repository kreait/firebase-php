# Authenticating requests to a Firebase app

You can authenticate requests either with a 
[Google Service Account JSON File](https://firebase.google.com/docs/server/setup#add_firebase_to_your_app)
or a database secret, which you can find in your Firebase project's settings.

```php
use Kreait\Firebase\Configuration;
use Kreait\Firebase\Firebase;

$config = new Configuration();
$config->setAuthConfigFile('/path/to/google-service-account.json');
// or
$config->setFirebaseSecret('my-firebase-secret');

$firebase = new Firebase('https://myapp.firebaseio.com', $config);
```

## Overriding authentication credentials

By default, Firebase transactions will be executed with the permissions defined for the Service Account, 
or as an admin user if you use the database secret.

You can override the authentication credentials with `setAuthOverride()`: 
 
```php

$firebase->setAuthOverride($uid, $claims = []);
$firebase->removeAuthOverride();
```

The `$claims` variable is optional. If set, it must be an associative array.
