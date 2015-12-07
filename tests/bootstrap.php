<?php

/*
 * This file is part of the firebase-php package.
 *
 * (c) Jérôme Gamez <jerome@kreait.com>
 * (c) kreait GmbH <info@kreait.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$loader = require __DIR__.'/../vendor/autoload.php';

// Anonymous function to avoid cluttering the global namespace
call_user_func(function () {
    // Add PHP Version to FIREBASE_BASE_LOCATION, if available
    if (getenv('TRAVIS_PHP_VERSION')) {
        $firebaseLocation = sprintf(
            '%s-php-%s', getenv('FIREBASE_BASE_LOCATION'), str_replace('.', '-', getenv('TRAVIS_PHP_VERSION'))
        );

        if (getenv('SCRUTINIZER') && strtolower(getenv('SCRUTINIZER')) === 'true') {
            $firebaseLocation = sprintf('%s-%s', getenv('FIREBASE_BASE_LOCATION'), 'scrutinizer');
        }

        putenv("FIREBASE_BASE_LOCATION=$firebaseLocation");
        $_ENV['FIREBASE_BASE_LOCATION'] = $firebaseLocation;
        $_SERVER['FIREBASE_BASE_LOCATION'] = $firebaseLocation;
    }

    $dotenv = new Dotenv\Dotenv(__DIR__);
    $dotenv->load();

    try {
        $dotenv->required([
            'FIREBASE_HOST', 'FIREBASE_BASE_LOCATION', 'FIREBASE_SECRET',
            'FIREBASE_TAPE_RECORDER_RECORDING_MODE', 'FIREBASE_TAPE_RECORDER_TAPES_DIR',
        ])->notEmpty();
    } catch (\RuntimeException $e) {
        throw new PHPUnit_Framework_Exception($e->getMessage());
    }

    // Push the firebase security rules to the configured application
    $host = getenv('FIREBASE_HOST');
    $secret = getenv('FIREBASE_SECRET');
    $baseLocation = getenv('FIREBASE_BASE_LOCATION');

    // Update firebase rules
    $rulesUri = sprintf('%s/.settings/rules.json?auth=%s', $host, $secret);
    $rules = file_get_contents(__DIR__.'/fixtures/firebase_rules.json');

    $http = \Ivory\HttpAdapter\HttpAdapterFactory::guess();
    $response = $http->put($rulesUri, [], $rules);

    if ($response->getStatusCode() >= 300) {
        // We count redirects as errors, too
        $jsonResponse = (string) $response->getBody();
        throw new PHPUnit_Framework_Exception(sprintf('Error while uploading the security rules: "%s"', $jsonResponse));
    }

    // Wipe data
    $dataUri = sprintf('%s/%s.json?auth=%s', $host, $baseLocation, $secret);
    $response = $http->delete($dataUri);
    if ($response->getStatusCode() >= 204) {
        $jsonResponse = (string) $response->getBody();
        throw new PHPUnit_Framework_Exception(sprintf('Problem while wiping all data from Firebase: "%s"', $jsonResponse));
    }
});
