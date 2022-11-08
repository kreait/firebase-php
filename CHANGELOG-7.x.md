# CHANGELOG

## [7.0.0] (Unreleased)

The most notable change is that you need PHP 8.1/8.2 to use the new version. The language migration of
the SDK has introduced some breaking changes concerning parameter types, which should not affect your
project in most cases.

* Dropped support for not actively supported PHP versions. Supported PHP versions are 8.1.x and 8.2.x
* Added direct dependency on `firebase/jwt`.
* Added support for verifying Firebase App Check Tokens. ([#693](https://github.com/kreait/firebase-php/issues/693))
* Added support for generating Firebase App Check Tokens for a custom provider.

See **[UPGRADE-7.0](UPGRADE-7.0.md) for information about relevant changes between 6.x and 7.0.**

## 6.x Changelog

https://github.com/kreait/firebase-php/blob/6.x/CHANGELOG.md

[Unreleased]: https://github.com/kreait/firebase-php/tree/7.x
[7.0.0]: https://github.com/kreait/firebase-php/releases/tag/7.0.0
