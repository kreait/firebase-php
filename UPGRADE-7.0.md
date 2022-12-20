# Upgrade from 6.x to 7.0

## Introduction

This is a major release, but its aim is to provide as much backward compatibility as possible to ease upgrades
from 6.x to 7.0.

The minimum required PHP version has been changed from `^7.4` to `~8.1.0 || ~8.2.0`, the two PHP versions that
are [actively supported since 2022-11-26](https://www.php.net/supported-versions.php).

If you're using version 6.x of the SDK as documented, an upgrade to 7.x should hopefully be as simple as updating the
version constraint in your project's `composer.json` file.

"As documented" means:

* When injecting SDK dependencies in your project's controllers and services, you use the interfaces in the 
  `Kreait\Firebase\Contract` namespace, not the (internal) implementations.
* You're not using classes marked as `@internal`
* You don't have created your own implementations based on the Interfaces in the `Kreait\Firebase\Contract` namespace.
  * Updating your custom implementations will take some work but should™ be straightforward.

## Notable changes

* The ability to disable credentials auto-discovery has been removed. If you don't want a service account to be
  auto-discovered, provide it by using the `withServiceAccount()` method of the Factory or by setting the
  `GOOGLE_APPLICATION_CREDENTIALS` environment variable. Depending on the environment in which the SDK is running,
  credentials could be auto-discovered otherwise, for example on GCP or GCE.
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
[BC] CHANGED: Class Kreait\Firebase\Auth\UserInfo became final
[BC] CHANGED: Class Kreait\Firebase\Auth\UserMetaData became final
[BC] CHANGED: Class Kreait\Firebase\Auth\UserRecord became final
[BC] CHANGED: Kreait\Firebase\Auth\CreateActionLink was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\CreateActionLink\GuzzleApiClientHandler was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\CreateActionLink\Handler was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\CreateSessionCookie was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\CreateSessionCookie\GuzzleApiClientHandler was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\CreateSessionCookie\Handler was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\DeleteUsersRequest was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\DeleteUsersResult::fromRequestAndResponse() was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\SendActionLink was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\SendActionLink\GuzzleApiClientHandler was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\SendActionLink\Handler was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\SignIn was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\SignInAnonymously was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\SignInWithCustomToken was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\SignInWithEmailAndOobCode was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\SignInWithEmailAndPassword was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\SignInWithIdpCredentials was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\SignInWithRefreshToken was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\TenantAwareAuthResourceUrlBuilder was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\UserInfo::fromResponseData() was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\UserMetaData::fromResponseData() was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Auth\UserRecord::fromResponseData() was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\Filter was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\Filter\EndAt was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\Filter\EndBefore was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\Filter\EqualTo was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\Filter\LimitToFirst was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\Filter\LimitToLast was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\Filter\Shallow was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\Filter\StartAfter was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\Filter\StartAt was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\Modifier was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\ModifierTrait was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\Sorter was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\Sorter\OrderByChild was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\Sorter\OrderByKey was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Query\Sorter\OrderByValue was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\Reference\Validator was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Database\UrlBuilder was marked "@internal"
[BC] CHANGED: Kreait\Firebase\DynamicLink\CreateDynamicLink\ApiRequest was marked "@internal"
[BC] CHANGED: Kreait\Firebase\DynamicLink\CreateDynamicLink\GuzzleApiClientHandler was marked "@internal"
[BC] CHANGED: Kreait\Firebase\DynamicLink\CreateDynamicLink\Handler was marked "@internal"
[BC] CHANGED: Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink\ApiRequest was marked "@internal"
[BC] CHANGED: Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink\GuzzleApiClientHandler was marked "@internal"
[BC] CHANGED: Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink\Handler was marked "@internal"
[BC] CHANGED: Kreait\Firebase\DynamicLink\ShortenLongDynamicLink\ApiRequest was marked "@internal"
[BC] CHANGED: Kreait\Firebase\DynamicLink\ShortenLongDynamicLink\GuzzleApiClientHandler was marked "@internal"
[BC] CHANGED: Kreait\Firebase\DynamicLink\ShortenLongDynamicLink\Handler was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Exception\HasErrors was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Messaging\Processor\SetApnsContentAvailableIfNeeded was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Messaging\Processor\SetApnsPushTypeIfNeeded was marked "@internal"
[BC] CHANGED: Kreait\Firebase\Messaging\RegistrationTokens#__construct() was marked "@internal"
[BC] CHANGED: Property Kreait\Firebase\Auth\UserRecord#$customClaims changed default value from array () to NULL
[BC] CHANGED: Property Kreait\Firebase\Auth\UserRecord#$disabled changed default value from false to NULL
[BC] CHANGED: Property Kreait\Firebase\Auth\UserRecord#$emailVerified changed default value from false to NULL
[BC] CHANGED: Property Kreait\Firebase\Auth\UserRecord#$providerData changed default value from array () to NULL
[BC] CHANGED: Property Kreait\Firebase\Auth\UserRecord#$uid changed default value from '' to NULL
[BC] CHANGED: The number of required arguments for Kreait\Firebase\Auth\UserRecord#__construct() increased from 0 to 15
[BC] CHANGED: The parameter $actionOrParametersOrUrl of Kreait\Firebase\Contract\DynamicLinks#createDynamicLink() changed from no type to Stringable|string|Kreait\Firebase\DynamicLink\CreateDynamicLink|array
[BC] CHANGED: The parameter $actionOrParametersOrUrl of Kreait\Firebase\Contract\DynamicLinks#createDynamicLink() changed from no type to a non-contravariant Stringable|string|Kreait\Firebase\DynamicLink\CreateDynamicLink|array
[BC] CHANGED: The parameter $clearTextPassword of Kreait\Firebase\Contract\Auth#signInWithEmailAndPassword() changed from no type to Stringable|string
[BC] CHANGED: The parameter $clearTextPassword of Kreait\Firebase\Contract\Auth#signInWithEmailAndPassword() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $clearTextPassword of Kreait\Firebase\Request\EditUserTrait#withClearTextPassword() changed from no type to Stringable|string
[BC] CHANGED: The parameter $clearTextPassword of Kreait\Firebase\Request\EditUserTrait#withClearTextPassword() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $clearTextPassword of Kreait\Firebase\Request\EditUserTrait#withClearTextPassword() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $clearTextPassword of Kreait\Firebase\Request\EditUserTrait#withClearTextPassword() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $data of Kreait\Firebase\DynamicLink\CreateDynamicLink#withAnalyticsInfo() changed from no type to a non-contravariant Kreait\Firebase\DynamicLink\AnalyticsInfo|array
[BC] CHANGED: The parameter $data of Kreait\Firebase\DynamicLink\CreateDynamicLink#withAndroidInfo() changed from no type to a non-contravariant Kreait\Firebase\DynamicLink\AndroidInfo|array
[BC] CHANGED: The parameter $data of Kreait\Firebase\DynamicLink\CreateDynamicLink#withIOSInfo() changed from no type to a non-contravariant Kreait\Firebase\DynamicLink\IOSInfo|array
[BC] CHANGED: The parameter $data of Kreait\Firebase\DynamicLink\CreateDynamicLink#withNavigationInfo() changed from no type to a non-contravariant Kreait\Firebase\DynamicLink\NavigationInfo|array
[BC] CHANGED: The parameter $data of Kreait\Firebase\DynamicLink\CreateDynamicLink#withSocialMetaTagInfo() changed from no type to a non-contravariant Kreait\Firebase\DynamicLink\SocialMetaTagInfo|array
[BC] CHANGED: The parameter $data of Kreait\Firebase\Messaging\CloudMessage#withData() changed from no type to a non-contravariant Kreait\Firebase\Messaging\MessageData|array
[BC] CHANGED: The parameter $dynamicLinkDomain of Kreait\Firebase\DynamicLink\CreateDynamicLink#withDynamicLinkDomain() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $dynamicLinkOrAction of Kreait\Firebase\Contract\DynamicLinks#getStatistics() changed from no type to Stringable|string|Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink
[BC] CHANGED: The parameter $dynamicLinkOrAction of Kreait\Firebase\Contract\DynamicLinks#getStatistics() changed from no type to a non-contravariant Stringable|string|Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink
[BC] CHANGED: The parameter $email of Kreait\Firebase\Auth\CreateActionLink::new() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#createUserWithEmailAndPassword() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#createUserWithEmailAndPassword() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#getEmailActionLink() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#getEmailActionLink() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#getEmailVerificationLink() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#getEmailVerificationLink() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#getPasswordResetLink() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#getPasswordResetLink() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#getSignInWithEmailLink() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#getSignInWithEmailLink() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#getUserByEmail() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#getUserByEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#sendEmailActionLink() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#sendEmailActionLink() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#sendEmailVerificationLink() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#sendEmailVerificationLink() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#sendPasswordResetLink() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#sendPasswordResetLink() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#sendSignInWithEmailLink() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#sendSignInWithEmailLink() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#signInWithEmailAndOobCode() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#signInWithEmailAndOobCode() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#signInWithEmailAndPassword() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Contract\Auth#signInWithEmailAndPassword() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withEmail() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withUnverifiedEmail() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withUnverifiedEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withUnverifiedEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withUnverifiedEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withVerifiedEmail() changed from no type to Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withVerifiedEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withVerifiedEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $email of Kreait\Firebase\Request\EditUserTrait#withVerifiedEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $idToken of Kreait\Firebase\Contract\Auth#createSessionCookie() changed from no type to Lcobucci\JWT\Token|string
[BC] CHANGED: The parameter $idToken of Kreait\Firebase\Contract\Auth#createSessionCookie() changed from no type to a non-contravariant Lcobucci\JWT\Token|string
[BC] CHANGED: The parameter $idToken of Kreait\Firebase\Contract\Auth#signInWithIdpIdToken() changed from no type to Lcobucci\JWT\Token|string
[BC] CHANGED: The parameter $idToken of Kreait\Firebase\Contract\Auth#signInWithIdpIdToken() changed from no type to a non-contravariant Lcobucci\JWT\Token|string
[BC] CHANGED: The parameter $idToken of Kreait\Firebase\Contract\Auth#verifyIdToken() changed from no type to Lcobucci\JWT\Token|string
[BC] CHANGED: The parameter $idToken of Kreait\Firebase\Contract\Auth#verifyIdToken() changed from no type to a non-contravariant Lcobucci\JWT\Token|string
[BC] CHANGED: The parameter $link of Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink::forLink() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $longDynamicLinkOrAction of Kreait\Firebase\Contract\DynamicLinks#shortenLongDynamicLink() changed from no type to Stringable|string|Kreait\Firebase\DynamicLink\ShortenLongDynamicLink|array
[BC] CHANGED: The parameter $longDynamicLinkOrAction of Kreait\Firebase\Contract\DynamicLinks#shortenLongDynamicLink() changed from no type to a non-contravariant Stringable|string|Kreait\Firebase\DynamicLink\ShortenLongDynamicLink|array
[BC] CHANGED: The parameter $message of Kreait\Firebase\Contract\Messaging#send() changed from no type to Kreait\Firebase\Messaging\Message|array
[BC] CHANGED: The parameter $message of Kreait\Firebase\Contract\Messaging#send() changed from no type to a non-contravariant Kreait\Firebase\Messaging\Message|array
[BC] CHANGED: The parameter $message of Kreait\Firebase\Contract\Messaging#sendMulticast() changed from no type to Kreait\Firebase\Messaging\Message|array
[BC] CHANGED: The parameter $message of Kreait\Firebase\Contract\Messaging#sendMulticast() changed from no type to a non-contravariant Kreait\Firebase\Messaging\Message|array
[BC] CHANGED: The parameter $message of Kreait\Firebase\Contract\Messaging#validate() changed from no type to Kreait\Firebase\Messaging\Message|array
[BC] CHANGED: The parameter $message of Kreait\Firebase\Contract\Messaging#validate() changed from no type to a non-contravariant Kreait\Firebase\Messaging\Message|array
[BC] CHANGED: The parameter $messages of Kreait\Firebase\Contract\Messaging#sendAll() changed from no type to a non-contravariant array|Kreait\Firebase\Messaging\Messages
[BC] CHANGED: The parameter $messages of Kreait\Firebase\Contract\Messaging#sendAll() changed from no type to array|Kreait\Firebase\Messaging\Messages
[BC] CHANGED: The parameter $newEmail of Kreait\Firebase\Contract\Auth#changeUserEmail() changed from no type to Stringable|string
[BC] CHANGED: The parameter $newEmail of Kreait\Firebase\Contract\Auth#changeUserEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $newPassword of Kreait\Firebase\Contract\Auth#changeUserPassword() changed from no type to Stringable|string
[BC] CHANGED: The parameter $newPassword of Kreait\Firebase\Contract\Auth#changeUserPassword() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $newPassword of Kreait\Firebase\Contract\Auth#confirmPasswordReset() changed from no type to Stringable|string
[BC] CHANGED: The parameter $newPassword of Kreait\Firebase\Contract\Auth#confirmPasswordReset() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $notification of Kreait\Firebase\Messaging\CloudMessage#withNotification() changed from no type to a non-contravariant Kreait\Firebase\Messaging\Notification|array
[BC] CHANGED: The parameter $password of Kreait\Firebase\Contract\Auth#createUserWithEmailAndPassword() changed from no type to Stringable|string
[BC] CHANGED: The parameter $password of Kreait\Firebase\Contract\Auth#createUserWithEmailAndPassword() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $phoneNumber of Kreait\Firebase\Contract\Auth#getUserByPhoneNumber() changed from no type to Stringable|string
[BC] CHANGED: The parameter $phoneNumber of Kreait\Firebase\Contract\Auth#getUserByPhoneNumber() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $properties of Kreait\Firebase\Contract\Auth#createUser() changed from no type to a non-contravariant array|Kreait\Firebase\Request\CreateUser
[BC] CHANGED: The parameter $properties of Kreait\Firebase\Contract\Auth#createUser() changed from no type to array|Kreait\Firebase\Request\CreateUser
[BC] CHANGED: The parameter $properties of Kreait\Firebase\Contract\Auth#updateUser() changed from no type to a non-contravariant array|Kreait\Firebase\Request\UpdateUser
[BC] CHANGED: The parameter $properties of Kreait\Firebase\Contract\Auth#updateUser() changed from no type to array|Kreait\Firebase\Request\UpdateUser
[BC] CHANGED: The parameter $provider of Kreait\Firebase\Contract\Auth#signInWithIdpAccessToken() changed from no type to Stringable|string
[BC] CHANGED: The parameter $provider of Kreait\Firebase\Contract\Auth#signInWithIdpAccessToken() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $provider of Kreait\Firebase\Contract\Auth#signInWithIdpIdToken() changed from no type to Stringable|string
[BC] CHANGED: The parameter $provider of Kreait\Firebase\Contract\Auth#signInWithIdpIdToken() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $provider of Kreait\Firebase\Contract\Auth#unlinkProvider() changed from no type to a non-contravariant array|Stringable|string
[BC] CHANGED: The parameter $provider of Kreait\Firebase\Contract\Auth#unlinkProvider() changed from no type to array|Stringable|string
[BC] CHANGED: The parameter $query of Kreait\Firebase\Contract\Auth#queryUsers() changed from no type to Kreait\Firebase\Auth\UserQuery|array
[BC] CHANGED: The parameter $query of Kreait\Firebase\Contract\Auth#queryUsers() changed from no type to a non-contravariant Kreait\Firebase\Auth\UserQuery|array
[BC] CHANGED: The parameter $registrationToken of Kreait\Firebase\Contract\Messaging#getAppInstance() changed from no type to Kreait\Firebase\Messaging\RegistrationToken|string
[BC] CHANGED: The parameter $registrationToken of Kreait\Firebase\Contract\Messaging#getAppInstance() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationToken|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#subscribeToTopic() changed from no type to Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#subscribeToTopic() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#subscribeToTopics() changed from no type to Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#subscribeToTopics() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#unsubscribeFromAllTopics() changed from no type to Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#unsubscribeFromAllTopics() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#unsubscribeFromTopic() changed from no type to Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#unsubscribeFromTopic() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#unsubscribeFromTopics() changed from no type to Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#unsubscribeFromTopics() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#validateRegistrationTokens() changed from no type to Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokenOrTokens of Kreait\Firebase\Contract\Messaging#validateRegistrationTokens() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokens of Kreait\Firebase\Contract\Messaging#sendMulticast() changed from no type to Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $registrationTokens of Kreait\Firebase\Contract\Messaging#sendMulticast() changed from no type to a non-contravariant Kreait\Firebase\Messaging\RegistrationTokens|Kreait\Firebase\Messaging\RegistrationToken|array|string
[BC] CHANGED: The parameter $token of Kreait\Firebase\Contract\Auth#signInWithCustomToken() changed from no type to Lcobucci\JWT\Token|string
[BC] CHANGED: The parameter $token of Kreait\Firebase\Contract\Auth#signInWithCustomToken() changed from no type to a non-contravariant Lcobucci\JWT\Token|string
[BC] CHANGED: The parameter $topic of Kreait\Firebase\Contract\Messaging#subscribeToTopic() changed from no type to a non-contravariant string|Kreait\Firebase\Messaging\Topic
[BC] CHANGED: The parameter $topic of Kreait\Firebase\Contract\Messaging#subscribeToTopic() changed from no type to string|Kreait\Firebase\Messaging\Topic
[BC] CHANGED: The parameter $topic of Kreait\Firebase\Contract\Messaging#unsubscribeFromTopic() changed from no type to a non-contravariant string|Kreait\Firebase\Messaging\Topic
[BC] CHANGED: The parameter $topic of Kreait\Firebase\Contract\Messaging#unsubscribeFromTopic() changed from no type to string|Kreait\Firebase\Messaging\Topic
[BC] CHANGED: The parameter $topic of Kreait\Firebase\Messaging\AppInstance#isSubscribedToTopic() changed from no type to a non-contravariant Kreait\Firebase\Messaging\Topic|string
[BC] CHANGED: The parameter $ttl of Kreait\Firebase\Contract\Auth#createCustomToken() changed from no type to a non-contravariant int|DateInterval|string
[BC] CHANGED: The parameter $ttl of Kreait\Firebase\Contract\Auth#createCustomToken() changed from no type to int|DateInterval|string
[BC] CHANGED: The parameter $ttl of Kreait\Firebase\Contract\Auth#createSessionCookie() changed from no type to DateInterval|int
[BC] CHANGED: The parameter $ttl of Kreait\Firebase\Contract\Auth#createSessionCookie() changed from no type to a non-contravariant DateInterval|int
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#changeUserEmail() changed from no type to Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#changeUserEmail() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#changeUserPassword() changed from no type to Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#changeUserPassword() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#createCustomToken() changed from no type to Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#createCustomToken() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#deleteUser() changed from no type to Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#deleteUser() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#disableUser() changed from no type to Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#disableUser() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#enableUser() changed from no type to Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#enableUser() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#getUser() changed from no type to Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#getUser() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#revokeRefreshTokens() changed from no type to Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#revokeRefreshTokens() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#setCustomUserClaims() changed from no type to Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#setCustomUserClaims() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#unlinkProvider() changed from no type to Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#unlinkProvider() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#updateUser() changed from no type to Stringable|string
[BC] CHANGED: The parameter $uid of Kreait\Firebase\Contract\Auth#updateUser() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $uri of Kreait\Firebase\Factory#withDatabaseUri() changed from no type to a non-contravariant Psr\Http\Message\UriInterface|string
[BC] CHANGED: The parameter $url of Kreait\Firebase\Contract\DynamicLinks#createShortLink() changed from no type to Stringable|string|Kreait\Firebase\DynamicLink\CreateDynamicLink|array
[BC] CHANGED: The parameter $url of Kreait\Firebase\Contract\DynamicLinks#createShortLink() changed from no type to a non-contravariant Stringable|string|Kreait\Firebase\DynamicLink\CreateDynamicLink|array
[BC] CHANGED: The parameter $url of Kreait\Firebase\Contract\DynamicLinks#createUnguessableLink() changed from no type to Stringable|string|Kreait\Firebase\DynamicLink\CreateDynamicLink|array
[BC] CHANGED: The parameter $url of Kreait\Firebase\Contract\DynamicLinks#createUnguessableLink() changed from no type to a non-contravariant Stringable|string|Kreait\Firebase\DynamicLink\CreateDynamicLink|array
[BC] CHANGED: The parameter $url of Kreait\Firebase\DynamicLink\CreateDynamicLink::forUrl() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $url of Kreait\Firebase\DynamicLink\ShortenLongDynamicLink::forLongDynamicLink() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $url of Kreait\Firebase\Request\EditUserTrait#withPhotoUrl() changed from no type to Stringable|string
[BC] CHANGED: The parameter $url of Kreait\Firebase\Request\EditUserTrait#withPhotoUrl() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $url of Kreait\Firebase\Request\EditUserTrait#withPhotoUrl() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $url of Kreait\Firebase\Request\EditUserTrait#withPhotoUrl() changed from no type to a non-contravariant Stringable|string
[BC] CHANGED: The parameter $user of Kreait\Firebase\Contract\Auth#signInAsUser() changed from no type to Kreait\Firebase\Auth\UserRecord|Stringable|string
[BC] CHANGED: The parameter $user of Kreait\Firebase\Contract\Auth#signInAsUser() changed from no type to a non-contravariant Kreait\Firebase\Auth\UserRecord|Stringable|string
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Query\Filter\EndAt#__construct() changed from no type to a non-contravariant bool|float|int|string
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Query\Filter\EndBefore#__construct() changed from no type to a non-contravariant int|float|string|bool
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Query\Filter\EqualTo#__construct() changed from no type to a non-contravariant bool|float|int|string
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Query\Filter\StartAfter#__construct() changed from no type to a non-contravariant int|float|string|bool
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Query\Filter\StartAt#__construct() changed from no type to a non-contravariant int|float|string|bool
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Query\ModifierTrait#appendQueryParam() changed from no type to mixed
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#endAt() changed from no type to a non-contravariant bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#endAt() changed from no type to bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#endBefore() changed from no type to a non-contravariant bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#endBefore() changed from no type to bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#equalTo() changed from no type to a non-contravariant bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#equalTo() changed from no type to bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#set() changed from no type to mixed
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#startAfter() changed from no type to a non-contravariant bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#startAfter() changed from no type to bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#startAt() changed from no type to a non-contravariant bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Reference#startAt() changed from no type to bool|string|int|float
[BC] CHANGED: The parameter $value of Kreait\Firebase\Database\Transaction#set() changed from no type to mixed
[BC] CHANGED: The parameter $value of Kreait\Firebase\Factory#withServiceAccount() changed from no type to a non-contravariant string|array
[BC] CHANGED: The parameter $versionNumber of Kreait\Firebase\Contract\RemoteConfig#getVersion() changed from no type to Kreait\Firebase\RemoteConfig\VersionNumber|int|string
[BC] CHANGED: The parameter $versionNumber of Kreait\Firebase\Contract\RemoteConfig#getVersion() changed from no type to a non-contravariant Kreait\Firebase\RemoteConfig\VersionNumber|int|string
[BC] CHANGED: The parameter $versionNumber of Kreait\Firebase\Contract\RemoteConfig#rollbackToVersion() changed from no type to Kreait\Firebase\RemoteConfig\VersionNumber|int|string
[BC] CHANGED: The parameter $versionNumber of Kreait\Firebase\Contract\RemoteConfig#rollbackToVersion() changed from no type to a non-contravariant Kreait\Firebase\RemoteConfig\VersionNumber|int|string
[BC] CHANGED: The return type of Kreait\Firebase\Contract\Database#runTransaction() changed from no type to mixed
[BC] CHANGED: The return type of Kreait\Firebase\Database\Query#getValue() changed from no type to mixed
[BC] CHANGED: The return type of Kreait\Firebase\Database\Query\Modifier#modifyValue() changed from no type to mixed
[BC] CHANGED: The return type of Kreait\Firebase\Database\Query\Modifier#modifyValue() changed from no type to mixed
[BC] CHANGED: The return type of Kreait\Firebase\Database\Query\Modifier#modifyValue() changed from no type to mixed
[BC] CHANGED: The return type of Kreait\Firebase\Database\Query\ModifierTrait#modifyValue() changed from no type to mixed
[BC] CHANGED: The return type of Kreait\Firebase\Database\Reference#getValue() changed from no type to mixed
[BC] CHANGED: The return type of Kreait\Firebase\Database\Snapshot#getValue() changed from no type to mixed
[BC] CHANGED: The return type of Kreait\Firebase\DynamicLink#previewUri() changed from Psr\Http\Message\UriInterface to the non-covariant Psr\Http\Message\UriInterface|null
[BC] CHANGED: Type of property Kreait\Firebase\Auth\UserInfo#$providerId changed from string|null to string
[BC] CHANGED: Type of property Kreait\Firebase\Auth\UserInfo#$uid changed from string|null to string
[BC] CHANGED: Type of property Kreait\Firebase\Auth\UserMetaData#$createdAt changed from DateTimeImmutable|null to DateTimeImmutable
[BC] REMOVED: Class Kreait\Firebase\Auth\CreateActionLink\ApiRequest has been deleted
[BC] REMOVED: Class Kreait\Firebase\Auth\CreateSessionCookie\ApiRequest has been deleted
[BC] REMOVED: Class Kreait\Firebase\Auth\SendActionLink\ApiRequest has been deleted
[BC] REMOVED: Class Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed has been deleted
[BC] REMOVED: Constant Kreait\Firebase\RemoteConfig\DefaultValue::IN_APP_DEFAULT_VALUE was removed
[BC] REMOVED: Method Kreait\Firebase\Auth\DeleteUsersResult::fromRequestAndResponse() was removed
[BC] REMOVED: Method Kreait\Firebase\Auth\UserInfo#jsonSerialize() was removed
[BC] REMOVED: Method Kreait\Firebase\Auth\UserInfo::fromResponseData() was removed
[BC] REMOVED: Method Kreait\Firebase\Auth\UserMetaData#jsonSerialize() was removed
[BC] REMOVED: Method Kreait\Firebase\Auth\UserMetaData::fromResponseData() was removed
[BC] REMOVED: Method Kreait\Firebase\Auth\UserRecord#__get() was removed
[BC] REMOVED: Method Kreait\Firebase\Auth\UserRecord#jsonSerialize() was removed
[BC] REMOVED: Method Kreait\Firebase\Auth\UserRecord::fromResponseData() was removed
[BC] REMOVED: Method Kreait\Firebase\Factory#withClientEmail() was removed
[BC] REMOVED: Method Kreait\Firebase\Factory#withDisabledAutoDiscovery() was removed
[BC] REMOVED: Method Kreait\Firebase\Messaging\AndroidConfig#withHighPriority() was removed
[BC] REMOVED: Method Kreait\Firebase\Messaging\AndroidConfig#withNormalPriority() was removed
[BC] REMOVED: Method Kreait\Firebase\Messaging\AndroidConfig#withPriority() was removed
[BC] REMOVED: Method Kreait\Firebase\Messaging\RegistrationTokens#__construct() was removed
[BC] REMOVED: Method Kreait\Firebase\RemoteConfig\DefaultValue#value() was removed
[BC] REMOVED: Method Kreait\Firebase\RemoteConfig\DefaultValue::none() was removed
[BC] REMOVED: Property Kreait\Firebase\Auth\UserInfo#$screenName was removed
[BC] REMOVED: These ancestors of Kreait\Firebase\Auth\UserInfo have been removed: ["JsonSerializable"]
[BC] REMOVED: These ancestors of Kreait\Firebase\Auth\UserMetaData have been removed: ["JsonSerializable"]
[BC] REMOVED: These ancestors of Kreait\Firebase\Auth\UserRecord have been removed: ["JsonSerializable"]
```
