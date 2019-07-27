<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\Auth\ApiConnectionFailed;
use Kreait\Firebase\Exception\Auth\AuthError;
use Kreait\Firebase\Exception\Auth\CredentialsMismatch;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\EmailNotFound;
use Kreait\Firebase\Exception\Auth\InvalidCustomToken;
use Kreait\Firebase\Exception\Auth\InvalidPassword;
use Kreait\Firebase\Exception\Auth\MissingPassword;
use Kreait\Firebase\Exception\Auth\OperationNotAllowed;
use Kreait\Firebase\Exception\Auth\PhoneNumberExists;
use Kreait\Firebase\Exception\Auth\UserDisabled;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\Auth\WeakPassword;
use Kreait\Firebase\Http\ErrorResponseParser;
use Throwable;

final class AuthApiExceptionConverter implements ExceptionConverter
{
    /** @var ErrorResponseParser */
    private $responseParser;

    public function __construct()
    {
        $this->responseParser = new ErrorResponseParser();
    }

    /**
     * @return AuthException
     */
    public function convertException(Throwable $exception): FirebaseException
    {
        if ($exception instanceof RequestException) {
            return $this->convertGuzzleRequestException($exception);
        }

        return new AuthError($exception->getMessage(), $exception->getCode(), $exception);
    }

    private function convertGuzzleRequestException(RequestException $e): AuthException
    {
        $message = $e->getMessage();

        if ($e instanceof ConnectException) {
            return new ApiConnectionFailed('Unable to connect to the API: '.$e->getMessage(), $e->getCode(), $e);
        }

        if ($response = $e->getResponse()) {
            $message = $this->responseParser->extractErrorReason($response);
        }

        if (\mb_stripos($message, 'credentials_mismatch') !== false) {
            return new CredentialsMismatch('Invalid custom token: The custom token corresponds to a different Firebase project.', $e->getCode(), $e);
        }

        if (\mb_stripos($message, 'email_exists') !== false) {
            return new EmailExists('The email address is already in use by another account.', $e->getCode(), $e);
        }

        if (\mb_stripos($message, 'email_not_found') !== false) {
            return new EmailNotFound('There is no user record corresponding to this identifier. The user may have been deleted.', $e->getCode(), $e);
        }

        if (\mb_stripos($message, 'invalid_custom_token') !== false) {
            return new InvalidCustomToken('Invalid custom token: The custom token format is incorrect or the token is invalid for some reason (e.g. expired, invalid signature, etc.)', $e->getCode(), $e);
        }

        if (\mb_stripos($message, 'invalid_password') !== false) {
            return new InvalidPassword('The password is invalid or the user does not have a password.', $e->getCode(), $e);
        }

        if (\mb_stripos($message, 'missing_password') !== false) {
            return new MissingPassword('Missing Password', $e->getCode(), $e);
        }

        if (\mb_stripos($message, 'operation_not_allowed') !== false) {
            return new OperationNotAllowed('Operation not allowed.', $e->getCode(), $e);
        }

        if (\mb_stripos($message, 'user_disabled') !== false) {
            return new UserDisabled('The user account has been disabled by an administrator.', $e->getCode(), $e);
        }

        if (\mb_stripos($message, 'user_not_found') !== false) {
            return new UserNotFound('There is no user record corresponding to this identifier. The user may have been deleted.', $e->getCode(), $e);
        }

        if (\mb_stripos($message, 'weak_password') !== false) {
            return new WeakPassword('The password must be 6 characters long or more.', $e->getCode(), $e);
        }

        if (\mb_stripos($message, 'phone_number_exists') !== false) {
            return new PhoneNumberExists('The phone number is already in use by another account.', $e->getCode(), $e);
        }

        return new AuthError($message, $e->getCode(), $e);
    }
}
