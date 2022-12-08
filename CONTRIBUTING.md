# Contributing

## Local Setup

### Prerequisites

1. PHP 8.1.0 or higher.
2. `composer` 2.4 or higher.

### Initial Setup

```shell
composer install
composer install-tools
```

### Running the Tests

The project is set up with PHPStan for static analysis as well as Unit tests and Integration tests.

```shell
# Run PHPStan and Unit Tests 
composer test
# Run PHPStan only
vendor/bin/phpstan
# Run Unit Tests only
vendor/bin/phpunit --testsuite=unit
# Run Integration Tests only
vendor/bin/phpunit --testsuite=integration
# Generate test coverage report into ./build/coverage
composer test-coverage
# Apply code style
composer cs
```

The integration tests in this project require a working firebase project and a number of setup steps to pass locally.

* Create a new [Firebase Project](https://console.firebase.google.com/).
* Generate and download a private key for a service account to `tests/_fixtures/test_credentials.json`.
* Create a Realtime Database and save its url as a JSON string to `tests/_fixtures/test_rtdb.json`.
* Follow these [instructions](https://github.com/firebase/quickstart-js/tree/master/messaging) on how to generate a
   Cloud Messaging device token and save the token as a JSON array to `tests/_fixtures/test_devices.json`.
* Setup [multi tenancy](https://cloud.google.com/identity-platform/docs/multi-tenancy-quickstart) create a tenant, and
   save its ID as a string to `tests/_fixtures/test_tenant.json`.
* Enable Email/Password Sign-in method in Firebase Authentication.
