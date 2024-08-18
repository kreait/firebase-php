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

> [!IMPORTANT]
> The SDK, along with its [Laravel Package](https://github.com/kreait/laravel-firebase) and 
> [Symfony Bundle](https://github.com/kreait/firebase-bundle), has garnered over 3,200 stars on GitHub. The SDK alone is
> [downloaded more than 650,000 times a month](https://packagist.org/packages/kreait/firebase-php/stats),
highlighting its significant impact and widespread use in the PHP community.
> 
> If your PHP project utilizes Firebase, there’s a high likelihood it’s leveraging this SDK. The impressive number of
> downloads underscores its integration into numerous CI/CD pipelines, signifying its critical role for many enterprises
> and agencies. 
>
> Despite its extensive use and the value it provides, the development and maintenance of this SDK have largely been unfunded.
>
> Over the past nine years, I have dedicated countless hours to developing and maintaining this SDK. This includes staying
> current with Firebase updates, supporting users, and [contributing significantly to official Google PHP libraries](https://github.com/pulls?user=googleapis&q=sort%3Acomments-desc+author%3Ajeromegamez&user=googleapis),
> ensuring all users benefit from up-to-date dependencies.
>
> While I am passionate about this work and thrilled by its utility to many, the lack of sponsorship has become
> increasingly challenging. To continue delivering high-quality updates and support, I need your help.
>
> If this SDK is valuable to your business, please consider [showing your appreciation through sponsorship](https://github.com/sponsors/jeromegamez).
>
> Your support will motivate me to continue enhancing and maintaining the SDK, ensuring it remains a valuable resource for everyone.
>
> To discuss additional sponsorship opportunities, please reach out to me at [funding@jerome.gamez.name](mailto:funding@jerome.gamez.name).

---

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

| Version | Initial Release | Supported PHP Versions           | Status       |
|---------|-----------------|----------------------------------|--------------|
| `7.x`   | 20 Dec 2022     | `~8.1.0, ~8.2.0, ~8.3.0, ~8.4.0` | Active       |
| `6.x`   | 01 Jul 2022     | `^7.4, ^8.0`                     | Paid support |
| `5.x`   | 01 Apr 2020     | `^7.2`                           | End of life  |
| `4.x`   | 14 Feb 2018     | `^7.0`                           | End of life  |
| `3.x`   | 22 Apr 2017     | `^7.0`                           | End of life  |
| `2.x`   | 06 Nov 2016     | `^7.0`                           | End of life  |
| `1.x`   | 15 Jul 2016     | `^5.5, ^7.0`                     | End of life  |
| `0.x`   | 09 Jan 2015     | `>=5.4`                          | End of life  |

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
