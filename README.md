# Firebase Admin SDK for PHP

Interact with [Google Firebase](https://firebase.google.com) from your PHP application.

[![Current version](https://img.shields.io/packagist/v/kreait/firebase-php.svg)](https://packagist.org/packages/kreait/firebase-php)
[![Supported PHP version](https://img.shields.io/packagist/php-v/kreait/firebase-php.svg)]()
[![Build Status](https://travis-ci.org/kreait/firebase-php.svg?branch=master)](https://travis-ci.org/kreait/firebase-php)
[![GitHub license](https://img.shields.io/github/license/kreait/firebase-php.svg)](https://github.com/kreait/firebase-php/blob/master/LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/kreait/firebase-php.svg)](https://packagist.org/packages/kreait/firebase-php/stats)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/kreait/firebase-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/kreait/firebase-php/?branch=master)
[![Gitter](https://badges.gitter.im/kreait/firebase-php.svg)](https://gitter.im/kreait/firebase-php)

## Documentation

You can find the full documentation at
[firebase-php.readthedocs.io](https://firebase-php.readthedocs.io/).

An example project can be found at https://github.com/jeromegamez/firebase-php-examples .

## Feature matrix

| Feature | PHP | Node.js | Java | Python | Go |
| --- | :---: | :---: | :---: | :---: | :---: |
| [Custom Token Minting](https://firebase.google.com/docs/auth/admin/create-custom-tokens) | ✅ | ✅ | ✅ | ✅ | ✅ |
| [ID Token Verification](https://firebase.google.com/docs/auth/admin/verify-id-tokens)	| ✅ | ✅ | ✅ | ✅ | ✅ |
| [Realtime Database API](https://firebase.google.com/docs/database/admin/start) | ✅* | ✅ | ✅ | ✅* | ✅ |
| [User Management API](https://firebase.google.com/docs/auth/admin/manage-users) | ✅ | ✅ | ✅ | ✅ | ✅ |
| [Remote Config](https://firebase.google.com/docs/remote-config/) | ✅ | | | | |
| [Cloud Messaging API](https://firebase.google.com/docs/cloud-messaging/admin/) | ✅ | ✅ | ✅ | ✅ | ✅ |				
| [Cloud Storage API](https://firebase.google.com/docs/storage/admin/start) | ✅ | ✅ | ✅ | ✅ | ✅ |
| [Cloud Firestore API](https://firebase.google.com/docs/firestore/) | # | ✅ | ✅ | ✅ | ✅ |

> \* The Realtime Database API currently does not support realtime event listeners.

> \# An integration with [google/cloud-firestore](https://github.com/GoogleCloudPlatform/google-cloud-php-firestore) 
  is currently not available to avoid the need to install the `grpc` PHP extension when using this SDK.
> [morrislaptop/firestore-php](https://github.com/morrislaptop/firestore-php) is a new project that aims to 
  provide support for the Firestore without the need to install the `grpc` PHP extension.

## Support

For bug reports and feature requests, use the [issue tracker](https://github.com/kreait/firebase-php/issues/).

For help with and discussion about the PHP SDK, join the [Gitter Channel dedicated to this library](https://gitter.im/kreait/firebase-php).

For questions about Firebase in general, use [Stack Overflow](https://stackoverflow.com/questions/tagged/firebase) or join the [Firebase Slack Community](https://firebase.community).
