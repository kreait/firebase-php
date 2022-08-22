<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\Auth\ApiConnectionFailed;
use Kreait\Firebase\Exception\Auth\AuthError;
use Kreait\Firebase\Exception\Auth\CredentialsMismatch;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\EmailNotFound;
use Kreait\Firebase\Exception\Auth\ExpiredOobCode;
use Kreait\Firebase\Exception\Auth\InvalidCustomToken;
use Kreait\Firebase\Exception\Auth\InvalidOobCode;
use Kreait\Firebase\Exception\Auth\InvalidPassword;
use Kreait\Firebase\Exception\Auth\MissingPassword;
use Kreait\Firebase\Exception\Auth\OperationNotAllowed;
use Kreait\Firebase\Exception\Auth\PhoneNumberExists;
use Kreait\Firebase\Exception\Auth\ProviderLinkFailed;
use Kreait\Firebase\Exception\Auth\UserDisabled;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\Auth\WeakPassword;
use Kreait\Firebase\Http\ErrorResponseParser;
use Psr\Http\Client\NetworkExceptionInterface;
use Throwable;

use function mb_stripos;

/**
 * @internal
 */
class AuthApiExceptionConverter
{
    private ErrorResponseParser $responseParser;

    public function __construct()
    {
        $this->responseParser = new ErrorResponseParser();
    }

    public function convertException(Throwable $exception): AuthException
    {
        if ($exception instanceof RequestException) {
            return $this->convertGuzzleRequestException($exception);
        }

        if ($exception instanceof NetworkExceptionInterface) {
            return new ApiConnectionFailed('Unable to connect to the API: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        return new AuthError($exception->getMessage(), $exception->getCode(), $exception);
    }

    private function convertGuzzleRequestException(RequestException $e): AuthException
    {
        $message = $e->getMessage();
        $code = $e->getCode();
        $response = $e->getResponse();

        if ($response !== null) {
            $message = $this->responseParser->getErrorReasonFromResponse($response);
            $code = $response->getStatusCode();
        }

        if (mb_stripos($message, 'credentials_mismatch') !== false) {
            return new CredentialsMismatch('Invalid custom token: The custom token corresponds to a different Firebase project.', $code, $e);
        }

        if (mb_stripos($message, 'email_exists') !== false) {
            return new EmailExists('The email address is already in use by another account.', $code, $e);
        }

        if (mb_stripos($message, 'email_not_found') !== false) {
            return new EmailNotFound('There is no user record corresponding to this identifier. The user may have been deleted.', $code, $e);
        }

        if (mb_stripos($message, 'invalid_custom_token') !== false) {
            return new InvalidCustomToken('Invalid custom token: The custom token format is incorrect or the token is invalid for some reason (e.g. expired, invalid signature, etc.)', $code, $e);
        }

        if (mb_stripos($message, 'invalid_password') !== false) {
            return new InvalidPassword('The password is invalid or the user does not have a password.', $code, $e);
        }

        if (mb_stripos($message, 'missing_password') !== false) {
            return new MissingPassword('Missing Password', $code, $e);
        }

        if (mb_stripos($message, 'operation_not_allowed') !== false) {
            return new OperationNotAllowed('Operation not allowed.', $code, $e);
        }

        if (mb_stripos($message, 'user_disabled') !== false) {
            return new UserDisabled('The user account has been disabled by an administrator.', $code, $e);
        }

        if (mb_stripos($message, 'user_not_found') !== false) {
            return new UserNotFound('There is no user record corresponding to this identifier. The user may have been deleted.', $code, $e);
        }

        if (mb_stripos($message, 'weak_password') !== false) {
            return new WeakPassword('The password must be 6 characters long or more.', $code, $e);
        }

        if (mb_stripos($message, 'phone_number_exists') !== false) {
            return new PhoneNumberExists('The phone number is already in use by another account.', $code, $e);
        }

        if (mb_stripos($message, 'invalid_idp_response') !== false) {
            return new ProviderLinkFailed('The supplied auth credential is malformed or has expired.', $code, $e);
        }

        if (mb_stripos($message, 'invalid_id_token') !== false) {
            return new ProviderLinkFailed('The user\'s credential is no longer valid. The user must sign in again.', $code, $e);
        }

        if (mb_stripos($message, 'federated_user_id_already_linked') !== false) {
            return new ProviderLinkFailed('This credential is already associated with a different user account.', $code, $e);
        }

        if (mb_stripos($message, 'expired_oob_code') !== false) {
            return new ExpiredOobCode('The action code has expired.', $code, $e);
        }

        if (mb_stripos($message, 'invalid_oob_code') !== false) {
            return new InvalidOobCode('The action code is invalid. This can happen if the code is malformed, expired, or has already been used.', $code, $e);
        }

        return new AuthError($message, $code, $e);
    }
}
