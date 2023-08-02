# Firebase Admin PHP SDK

[![Current version](https://img.shields.io/packagist/v/kreait/firebase-php.svg?logo=composer)](https://packagist.org/packages/kreait/firebase-php)
[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/kreait/firebase-php)](https://packagist.org/packages/kreait/firebase-php)
[![Monthly Downloads](https://img.shields.io/packagist/dm/kreait/firebase-php.svg)](https://packagist.org/packages/kreait/firebase-php/stats)
[![Total Downloads](https://img.shields.io/packagist/dt/kreait/firebase-php.svg)](https://packagist.org/packages/kreait/firebase-php/stats)
[![Tests](https://github.com/kreait/firebase-php/actions/workflows/tests.yml/badge.svg)](https://github.com/kreait/firebase-php/actions/workflows/tests.yml)
[![Integration Tests](https://github.com/kreait/firebase-php/actions/workflows/integration-tests.yml/badge.svg)](https://github.com/kreait/firebase-php/actions/workflows/integration-tests.yml)
[![Emulator Tests](https://github.com/kreait/firebase-php/actions/workflows/emulator-tests.yml/badge.svg)](https://github.com/kreait/firebase-php/actions/workflows/emulator-tests.yml)
[![codecov](https://codecov.io/gh/kreait/firebase-php/branch/main/graph/badge.svg)](https://codecov.io/gh/kreait/firebase-php)
[![Sponsor](https://img.shields.io/static/v1?logo=GitHub&label=Sponsor&message=%E2%9D%A4&color=ff69b4)](https://github.com/sponsors/jeromegamez)

## Table of Contents

- [Overview](#overview)
- [Installation](#installation)
- [Supported Versions](#supported-versions)
- [Fund this project](#fund-this-project)
- [License](#license)

## Overview

[Firebase](https://firebase.google.com/) provides the tools and infrastructure you need to develop your app, grow your user base, and earn money. The Firebase Admin PHP SDK enables access to Firebase services from privileged environments (such as servers or cloud) in PHP.

For more information, visit the [Firebase Admin PHP SDK documentation](https://firebase-php.readthedocs.io/).


## Installation

The Firebase Admin PHP SDK is available on Packagist as [`kreait/firebase-php`](https://packagist.org/packages/kreait/firebase-php):

```bash
composer require "kreait/firebase-php:^7.0" 
```

## Supported Versions

**Only the latest version is actively supported.**

Earlier versions will receive security fixes as long as their **lowest** PHP requirement receives security fixes. For
example, when a version supports PHP 7.4 and PHP 8.0, security support will end when security support for PHP 7.4 ends.

| Version | Initial Release | Supported PHP Versions | Status                                 |
|---------|-----------------|------------------------|----------------------------------------|
| `7.x`   | 20 Dec 2022     | `~8.1.0, ~8.2.0`       | Active support                         |
| `6.x`   | 01 Jul 2022     | `^7.4, ^8.0`           | End of life                            |
| `5.x`   | 01 Apr 2020     | `^7.2`                 | End of life                            |
| `4.x`   | 14 Feb 2018     | `^7.0`                 | End of life                            |
| `3.x`   | 22 Apr 2017     | `^7.0`                 | End of life                            |
| `2.x`   | 06 Nov 2016     | `^7.0`                 | End of life                            |
| `1.x`   | 15 Jul 2016     | `^5.5, ^7.0`           | End of life                            |
| `0.x`   | 09 Jan 2015     | `>=5.4`                | End of life                            |

See [Support for older versions of the SDK](#support-for-older-versions-of-the-sdk) if you need support for an older version.

## Fund this project

This project has been downloaded millions of times and is used in many commercial projects. Support its development
and keep it sustainable by becoming a [GitHub Sponsor](https://github.com/sponsors/jeromegamez).

If you have feature requests and support questions other than bugfix reports, you can ask them by becoming a 
[GitHub Sponsor with an at least $50+ monthly tier](https://github.com/sponsors/jeromegamez?frequency=recurring),
and creating a new issue. Support questions must be discussed publicly, I do not provide free 1:1 support in non-public
channels.

Sponsorships are regarded as appreciation for the time and work I invested into this project, with no strings attached. 

Please contact [@jeromegamez](https://github.com/jeromegamez) to discuss alternatives to GitHub sponsorships.

### Support for older versions of the SDK

If you are a [GitHub Sponsor with an at least $100+ monthly tier](https://github.com/sponsors/jeromegamez?frequency=recurring),
**all issues** created by you will be addressed with priority, including issues related to an earlier version.

After making a [one-time 100$ sponsorship](https://github.com/sponsors/jeromegamez?frequency=one-time),
**one** issue created by you will be addressed with priority, including issues related to an earlier version.

If an issue or feature request requires changes in an earlier version, we can discuss a one-time sponsorship amount for
implementing it.

## License

Firebase Admin PHP SDK is licensed under the [MIT License](LICENSE).

Your use of Firebase is governed by the [Terms of Service for Firebase Services](https://firebase.google.com/terms/).
