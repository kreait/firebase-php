# CHANGELOG

## [7.0.0] (Unreleased)

The most notable change is that you need PHP 8.1/8.2 to use the new version. The language migration of
the SDK introduces breaking changes concerning the strictness of parameter types almost everywhere in
the SDK - however, this should not affect your project in most cases (unless you have used the SDK's 
internal classes directly or extended them).

This release adds many more PHPDoc annotations to support the usage of Static Analysis Tools like PHPStan
and Psalm and moves away from doing runtime checks. It is strongly recommended to use a Static Analysis
Tool and ensure that input values are validated before handing them over to the SDK.

See **[UPGRADE-7.0](UPGRADE-7.0.md) for more details on the changes between 6.x and 7.0.**

## 6.x Changelog

https://github.com/kreait/firebase-php/blob/6.x/CHANGELOG.md

<!-- [Unreleased]: https://github.com/kreait/firebase-php/tree/7.x -->
<!-- [7.0.0]: https://github.com/kreait/firebase-php/releases/tag/7.0.0 -->
[7.0.0]: https://github.com/kreait/firebase-php/tree/7.x
