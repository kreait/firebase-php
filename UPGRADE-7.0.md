# Upgrade from 6.x to 7.0

## Introduction

This is a major release, but its aim is to provide as much backward compatibility as possible to ease upgrades
from 6.x to 7.0.

The most notable change between 6.x and 7.0 is that the minimum required PHP version has been changed from 
`^7.4` to `~8.1.0 || ~8.2.0`, the two PHP versions that are [actively supported since 2022-11-26](https://www.php.net/supported-versions.php).

If you're using version 6.x of the SDK in the documented way, an upgrade to 7.x should be as simple as updating the
version constraint in your project's `composer.json` file.

"In the documented way" means:

* When injecting SDK dependencies in your project's controllers and services, you use the interfaces in the 
  `Kreait\Firebase\Contract` namespace, not the (internal) implementations.
* You're not using classes marked as `@internal`
* You don't have created your own implementations based on the Interfaces in the `Kreait\Firebase\Contract` namespace.
  * Updating your custom implementations will take some work but should™ be straightforward.

## Notable changes

* Classes, Methods and Constants marked as `@deprecated` have been removed.
* Classes, Methods and Constants marked as `@internal` have been removed or refactored.
* `@param` annotations in PHPDoc blocks have been moved to typed parameters, for example:
  ```php
  /**
   * @param scalar $value
   */
  public function method($value);
  ```
  would change to
  ```php
  public function method(bool|string|int|float $value);
  ```
* Methods that accepted strings and objects with a `__toString()` method have been replaced with `Stringable|string`, 
  for example:
  ```php
  /**
   * @param Uid|string $uid
   */
  public function method($uid);
  ```
  have been changed to
  ```php
  public function method(Stringable|string $value);
  ```

## Complete list of breaking changes

The following list has been generated with [roave/backward-compatibility-check](https://github.com/Roave/BackwardCompatibilityCheck).

### Removals

```
[BC] REMOVED: Class Kreait\Firebase\Auth\SendActionLink\ApiRequest has been deleted
[BC] REMOVED: Class Kreait\Firebase\Auth\CreateActionLink\ApiRequest has been deleted
[BC] REMOVED: Class Kreait\Firebase\Auth\CreateSessionCookie\ApiRequest has been deleted
[BC] REMOVED: Constant Kreait\Firebase\RemoteConfig\DefaultValue::IN_APP_DEFAULT_VALUE was removed
[BC] REMOVED: Method Kreait\Firebase\RemoteConfig\DefaultValue::none() was removed
[BC] REMOVED: Method Kreait\Firebase\RemoteConfig\DefaultValue#value() was removed
[BC] REMOVED: Method Kreait\Firebase\Messaging\AndroidConfig#withHighPriority() was removed
[BC] REMOVED: Method Kreait\Firebase\Messaging\AndroidConfig#withNormalPriority() was removed
[BC] REMOVED: Method Kreait\Firebase\Messaging\AndroidConfig#withPriority() was removed
```

### Changes

```
[BC] CHANGED: The parameter $value of Kreait\Firebase\Factory#withServiceAccount() changed from no type to a non-contravariant string|array
[BC] CHANGED: The parameter $uri of Kreait\Firebase\Factory#withDatabaseUri() changed from no type to a non-contravariant Psr\Http\Message\UriInterface|string
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Transaction#set() changed from no type to mixed
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#startAt() changed from no type to a non-contravariant bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#startAt() changed from no type to bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#startAfter() changed from no type to a non-contravariant bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#startAfter() changed from no type to bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#endAt() changed from no type to a non-contravariant bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#endAt() changed from no type to bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#endBefore() changed from no type to a non-contravariant bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#endBefore() changed from no type to bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#equalTo() changed from no type to a non-contravariant bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#equalTo() changed from no type to bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#set() changed from no type to mixed
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Query\ModifierTrait#appendQueryParam() changed from no type to mixed
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Query\Filter\StartAfter#__construct() changed from no type to a non-contravariant int|float|string|bool
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Query\Filter\EndAt#__construct() changed from no type to a non-contravariant bool|float|int|string
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Query\Filter\StartAt#__construct() changed from no type to a non-contravariant int|float|string|bool
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Query\Filter\EndBefore#__construct() changed from no type to a non-contravariant int|float|string|bool
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Query\Filter\EqualTo#__construct() changed from no type to a non-contravariant bool|float|int|string
[BC] CHANGED: The parameter $message of Kreait\Firebase\Contract\Messaging#send() changed from no type to a non-contravariant Kreait\Firebase\Messaging\Message|array
[BC] CHANGED: The parameter $message of Kreait\Firebase\Contract\Messaging#send() changed from no type to Kreait\Firebase\Messaging\Message|array
[BC] CHANGED: The parameter $message of Kreait\Firebase\Contract\Messaging#sendMulticast() changed from no type to a non-contravariant Kreait\Firebase\Messaging\Message|array
[BC] CHANGED: The parameter $registrationTokens of Kreait\Firebase\Contract\Messaging#sendMulticast() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $message of Kreait\Firebase\Contract\Messaging#sendMulticast() changed from no type to Kreait\Firebase\Messaging\Message|array
[BC] CHANGED: The parameter $registrationTokens of Kreait\Firebase\Contract\Messaging#sendMulticast() changed from no type to Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $messages of Kreait\Firebase\Contract\Messaging#sendAll() changed from no type to a non-contravariant array|Kreait\Firebase\Messaging\Messages
[BC] CHANGED: The parameter $messages of Kreait\Firebase\Contract\Messaging#sendAll() changed from no type to array|Kreait\Firebase\Messaging\Messages
[BC] CHANGED: The parameter $message of Kreait\Firebase\Contract\Messaging#validate() changed from no type to a non-contravariant Kreait\Firebase\Messaging\Message|array
[BC] CHANGED: The parameter $message of Kreait\Firebase\Contract\Messaging#validate() changed from no type to Kreait\Firebase\Messaging\Message|array
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#validateRegistrationTokens() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#validateRegistrationTokens() changed from no type to Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $topic of Kreait\Firebase\Contract\Messaging#subscribeToTopic() changed from no type to a non-contravariant string|Kreait\Firebase\Messaging\Topic
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#subscribeToTopic() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $topic of Kreait\Firebase\Contract\Messaging#subscribeToTopic() changed from no type to string|Kreait\Firebase\Messaging\Topic
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#subscribeToTopic() changed from no type to Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#subscribeToTopics() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#subscribeToTopics() changed from no type to Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $topic of Kreait\Firebase\Contract\Messaging#unsubscribeFromTopic() changed from no type to a non-contravariant string|Kreait\Firebase\Messaging\Topic
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#unsubscribeFromTopic() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $topic of Kreait\Firebase\Contract\Messaging#unsubscribeFromTopic() changed from no type to string|Kreait\Firebase\Messaging\Topic
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#unsubscribeFromTopic() changed from no type to Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#unsubscribeFromTopics() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#unsubscribeFromTopics() changed from no type to Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#unsubscribeFromAllTopics() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#unsubscribeFromAllTopics() changed from no type to Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationToken of Kreait\Firebase\Contract\Messaging#getAppInstance() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationToken|string
[BC] CHANGED: The parameter $registrationToken of Kreait\Firebase\Contract\Messaging#getAppInstance() changed from no type to Kreait\Firebase\Messaging\RegistrationToken|string
[BC] CHANGED: The parameter $url of Kreait\Firebase\Contract\DynamicLinks#createUnguessableLink() changed from no type to a non-contravariant Stringable|string|Kreait\Firebase\DynamicLink\CreateDynamicLink|array
[BC] CHANGED: The parameter $url of Kreait\Firebase\Contract\DynamicLinks#createUnguessableLink() changed from no type to Stringable|string|Kreait\Firebase\DynamicLink\CreateDynamicLink|array
[BC] CHANGED: The parameter $url of Kreait\Firebase\Contract\DynamicLinks#createShortLink() changed from no type to a non-contravariant Stringable|string|Kreait\Firebase\DynamicLink\CreateDynamicLink|array
[BC] CHANGED: The parameter $url of Kreait\Firebase\Contract\DynamicLinks#createShortLink() changed from no type to Stringable|string|Kreait\Firebase\DynamicLink\CreateDynamicLink|array
[BC] CHANGED: The parameter $actionOrParametersOrUrl of Kreait\Firebase\Contract\DynamicLinks#createDynamicLink() changed from no type to a non-contravariant Stringable|string|Kreait\Firebase\DynamicLink\CreateDynamicLink|array
[BC] CHANGED: The parameter $actionOrParametersOrUrl of Kreait\Firebase\Contract\DynamicLinks#createDynamicLink() changed from no type to Stringable|string|Kreait\Firebase\DynamicLink\CreateDynamicLink|array
[BC] CHANGED: The parameter $longDynamicLinkOrAction of Kreait\Firebase\Contract\DynamicLinks#shortenLongDynamicLink() changed from no type to a non-contravariant Stringable|string|Kreait\Firebase\DynamicLink\ShortenLongDynamicLink|array
[BC] CHANGED: The parameter $longDynamicLinkOrAction of Kreait\Firebase\Contract\DynamicLinks#shortenLongDynamicLink() changed from no type to Stringable|string|Kreait\Firebase\DynamicLink\ShortenLongDynamicLink|array
[BC] CHANGED: The parameter $dynamicLinkOrAction of Kreait\Firebase\Contract\DynamicLinks#getStatistics() changed from no type to a non-contravariant Stringable|string|Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink
[BC] CHANGED: The parameter $dynamicLinkOrAction of Kreait\Firebase\Contract\DynamicLinks#getStatistics() changed from no type to Stringable|string|Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink
[BC] CHANGED: The parameter $versionNumber of Kreait\Firebase\Contract\RemoteConfig#getVersion() changed from no type to a non-contravariant Kreait\Firebase\RemoteConfig\VersionNumber|int|string
[BC] CHANGED: The parameter $versionNumber of Kreait\Firebase\Contract\RemoteConfig#getVersion() changed from no type to Kreait\Firebase\RemoteConfig\VersionNumber|int|string
[BC] CHANGED: The parameter $versionNumber of Kreait\Firebase\Contract\RemoteConfig#rollbackToVersion() changed from no type to a non-contravariant Kreait\Firebase\RemoteConfig\VersionNumber|int|string
[BC] CHANGED: The parameter $versionNumber of Kreait\Firebase\Contract\RemoteConfig#rollbackToVersion() changed from no type to Kreait\Firebase\RemoteConfig\VersionNumber|int|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Auth\CreateActionLink::new() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $url of Kreait\Firebase\DynamicLink\CreateDynamicLink::forUrl() changed from no type to a non-contravariant string|Stringable
[BC] CHANGED: The parameter $dynamicLinkDomain of Kreait\Firebase\DynamicLink\CreateDynamicLink#withDynamicLinkDomain() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $data of Kreait\Firebase\DynamicLink\CreateDynamicLink#withAnalyticsInfo() changed from no type to a non-contravariant Kreait\Firebase\DynamicLink\AnalyticsInfo|array
[BC] CHANGED: The parameter $data of Kreait\Firebase\DynamicLink\CreateDynamicLink#withAndroidInfo() changed from no type to a non-contravariant Kreait\Firebase\DynamicLink\AndroidInfo|array
[BC] CHANGED: The parameter $data of Kreait\Firebase\DynamicLink\CreateDynamicLink#withIOSInfo() changed from no type to a non-contravariant Kreait\Firebase\DynamicLink\IOSInfo|array
[BC] CHANGED: The parameter $data of Kreait\Firebase\DynamicLink\CreateDynamicLink#withNavigationInfo() changed from no type to a non-contravariant Kreait\Firebase\DynamicLink\NavigationInfo|array
[BC] CHANGED: The parameter $data of Kreait\Firebase\DynamicLink\CreateDynamicLink#withSocialMetaTagInfo() changed from no type to a non-contravariant Kreait\Firebase\DynamicLink\SocialMetaTagInfo|array
[BC] CHANGED: The parameter $url of Kreait\Firebase\DynamicLink\ShortenLongDynamicLink::forLongDynamicLink() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $link of Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink::forLink() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withVerifiedEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withUnverifiedEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $url of Kreait\Firebase\Request\EditUserTrait#withPhotoUrl() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $clearTextPassword of Kreait\Firebase\Request\EditUserTrait#withClearTextPassword() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withVerifiedEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withUnverifiedEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $url of Kreait\Firebase\Request\EditUserTrait#withPhotoUrl() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $clearTextPassword of Kreait\Firebase\Request\EditUserTrait#withClearTextPassword() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withEmail() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withVerifiedEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withVerifiedEmail() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withUnverifiedEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withUnverifiedEmail() changed from no type to Stringable|string
[BC] CHANGED: The parameter $url of Kreait\Firebase\Request\EditUserTrait#withPhotoUrl() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $url of Kreait\Firebase\Request\EditUserTrait#withPhotoUrl() changed from no type to Stringable|string
[BC] CHANGED: The parameter $clearTextPassword of Kreait\Firebase\Request\EditUserTrait#withClearTextPassword() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $clearTextPassword of Kreait\Firebase\Request\EditUserTrait#withClearTextPassword() changed from no type to Stringable|string
```
