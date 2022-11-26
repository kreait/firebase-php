# Contributing

## Local Setup

### Prerequisites

1. PHP 8.1.0 or higher.
2. `composer` 2.4 or higher.

### Initial Setup

    composer install
    composer install-tools

### Running the Tests

The project is setup with PHPStan for static analysis as well as Unit tests and Integration tests.

To run PHPStan and the Unit tests, simply run the following:

    composer test

The integration tests in this project require a working firebase project and a number of setup steps to pass locally.

1. Create a new [Firebase Project](https://console.firebase.google.com/).
2. Generate and download a private key for a service account to `tests/_fixtures/test_credentials.json`.
3. Create a Realtime Database and save the url to `tests/_fixtures/test_rtdb.json`.
4. Follow these [instructions](https://github.com/firebase/quickstart-js/tree/master/messaging) on how to generate a Cloud Messaging device token and save the token as a JSON array to `tests/_fixtures/test_devices.json`.
5. Setup [multi tenancy](https://cloud.google.com/identity-platform/docs/multi-tenancy-quickstart) in Google Identity provider for the test project and save the Firebase app id to `tests/_fixtures/test_app.json`.
6. Enable Email/Password Sign-in method in Firebase Authentication.
7. Enable App Check for the app in the Firebase console.
