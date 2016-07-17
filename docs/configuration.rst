#############
Configuration
#############

.. code-block:: php

    use Kreait\Firebase\Configuration;
    use Kreait\Firebase\Firebase;

    $config = new Configuration();

    $firebase = new Firebase('https://my-app.firebaseio.com', $config);

*******
Logging
*******

Any `PSR-3 compliant logger <https://packagist.org/providers/psr/log-implementation>`_
can be used for logging. The following example uses
`Monolog <https://github.com/Seldaek/monolog>`_:

.. code-block:: php

    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;

    $logger = new Logger('firebase-php');
    $logger->pushHandler(new StreamHandler('path/to/your.log', Logger::DEBUG));

    $config->setLogger($logger);

*******************
Custom HTTP Adapter
*******************

By default, the library tries to find an already existing instance of an
HTTP adapter, and if none is found, it will create a new one. You can
override the used HTTP adapter by setting it in the configuration like this:

.. code-block:: php

    use Ivory\HttpAdapter\FopenHttpAdapter;

    $http = new FopenHttpAdapter(); // or any other available adapter

    $config->setHttpAdapter($http);
