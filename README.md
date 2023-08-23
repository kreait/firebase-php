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

---

## The future of the Firebase Admin PHP SDK

Development of this SDK has cost thousands of hours of work and the vast majority
of work has been done by one, mostly unpaid, contributor, [@jeromegamez](https://github.com/jeromegamez).

The number of monthly downloads shows that many corporate/agency users rely heavily
on the SDK; however, very few have contributed, and none have given back and 
funded this project.

**Unless funding is found to continue maintaining the SDK, maintenance will be halted.**

The funding goal is a **recurring $5,000/month**. Reaching and maintaining this goal will allow me
to continue maintaining and developing the SDK. You can see the current progress on reaching this
goal on [@jeromegamez's Sponsor Page](https://github.com/sponsors/jeromegamez). Funding outside of
GitHub will reduce the goal.

This is a call to action for the business users of the SDK to figure out a way to
fund the continued maintenance and development of the SDK, as the one person
on which the whole project leans is done with the current status quo.

If you want to help change the situation, please reach out to
[@jeromegamez](https://github.com/jeromegamez) to discuss,
and/or [become a GitHub Sponsor](https://github.com/sponsors/jeromegamez).

_(This text is based on a similar announcement by [@jrfnl](https://github.com/jrfnl) on
the [WordpressCS 3.0 Release Page](https://make.wordpress.org/core/2023/08/21/wordpresscs-3-0-0-is-now-available/)_)

---

- [Overview](#overview)
- [Installation](#installation)
- [Supported Versions](#supported-versions)
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

| Version | Initial Release | Supported PHP Versions | Status      |
|---------|-----------------|------------------------|-------------|
| `7.x`   | 20 Dec 2022     | `~8.1.0, ~8.2.0`       | Halted      |
| `6.x`   | 01 Jul 2022     | `^7.4, ^8.0`           | End of life |
| `5.x`   | 01 Apr 2020     | `^7.2`                 | End of life |
| `4.x`   | 14 Feb 2018     | `^7.0`                 | End of life |
| `3.x`   | 22 Apr 2017     | `^7.0`                 | End of life |
| `2.x`   | 06 Nov 2016     | `^7.0`                 | End of life |
| `1.x`   | 15 Jul 2016     | `^5.5, ^7.0`           | End of life |
| `0.x`   | 09 Jan 2015     | `>=5.4`                | End of life |

<table>
    <body>
        <tr>
            <td><img src="https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.png" width="50" alt="JetBrains Logo"></td>
            <td>A big thank you to <a href="https://www.jetbrains.com">JetBrains</a> for supporting this project with free open-source licences of their IDEs.</td>
        </tr>
    </body>
</table>

## License

Firebase Admin PHP SDK is licensed under the [MIT License](LICENSE).

Your use of Firebase is governed by the [Terms of Service for Firebase Services](https://firebase.google.com/terms/).
