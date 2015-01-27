# Use your own HTTP client

The Firebase client uses the [HTTP Adapter](https://github.com/egeloen/ivory-http-adapter) by Eric Geloen which enables support for a multitude of HTTP clients. If you want to override the default HTTP Client (cURL) used by Firebase, you can use [one of the supported HTTP adapters](https://github.com/egeloen/ivory-http-adapter/blob/master/doc/adapters.md) and use it as the second parameter when creating a Firebase instance:

```php
use Ivory\HttpAdapter\FopenHttpAdapter;
use Kreait\Firebase\Firebase;

$http = new FopenHttpAdapter();
$firebase = new Firebase('https://brilliant-torch-1474.firebaseio.com', $http);
```
