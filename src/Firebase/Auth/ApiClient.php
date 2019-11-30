<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Exception\Auth\CredentialsMismatch;
use Kreait\Firebase\Exception\Auth\EmailNotFound;
use Kreait\Firebase\Exception\Auth\InvalidCustomToken;
use Kreait\Firebase\Exception\AuthApiExceptionConverter;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Http\WrappedGuzzleClient;
use Kreait\Firebase\Request;
use Kreait\Firebase\Value\Provider;
use Lcobucci\JWT\Token;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @internal
 */
class ApiClient implements ClientInterface
{
    use WrappedGuzzleClient;

    /** @var AuthApiExceptionConverter */
    private $errorHandler;

    /**
     * @internal
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->errorHandler = new AuthApiExceptionConverter();
    }

    /**
     * Takes a custom token and exchanges it with an ID token.
     *
     * @see https://firebase.google.com/docs/reference/rest/auth/#section-verify-custom-token
     *
     * @throws InvalidCustomToken
     * @throws CredentialsMismatch
     * @throws AuthException
     * @throws FirebaseException
     */
    public function exchangeCustomTokenForIdAndRefreshToken(Token $token): ResponseInterface
    {
        return $this->requestApi('verifyCustomToken', [
            'token' => (string) $token,
            'returnSecureToken' => true,
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function createUser(Request\CreateUser $request): ResponseInterface
    {
        return $this->requestApi('signupNewUser', $request);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function updateUser(Request\UpdateUser $request): ResponseInterface
    {
        return $this->requestApi('setAccountInfo', $request);
    }

    /**
     * @deprecated 4.2.0
     * @see ApiClient::createUser()
     *
     * @codeCoverageIgnore
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function signupNewUser(string $email = null, string $password = null): ResponseInterface
    {
        \trigger_error(__METHOD__.' is deprecated.', \E_USER_DEPRECATED);

        $request = Request\CreateUser::new();

        if ($email) {
            $request = $request->withUnverifiedEmail($email);
        }

        if ($password) {
            $request = $request->withClearTextPassword($password);
        }

        return $this->createUser($request);
    }

    /**
     * Returns a user for the given email address.
     *
     * @throws EmailNotFound
     * @throws AuthException
     * @throws FirebaseException
     */
    public function getUserByEmail(string $email): ResponseInterface
    {
        return $this->requestApi('getAccountInfo', [
            'email' => [$email],
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function getUserByPhoneNumber(string $phoneNumber): ResponseInterface
    {
        return $this->requestApi('getAccountInfo', [
            'phoneNumber' => [$phoneNumber],
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function downloadAccount(int $batchSize = null, string $nextPageToken = null): ResponseInterface
    {
        $batchSize = $batchSize ?? 1000;

        return $this->requestApi('downloadAccount', \array_filter([
            'maxResults' => $batchSize,
            'nextPageToken' => $nextPageToken,
        ]));
    }

    /**
     * @deprecated 4.2.0
     * @see ApiClient::updateUser()
     *
     * @param mixed $uid
     *
     * @codeCoverageIgnore
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function enableUser($uid): ResponseInterface
    {
        \trigger_error(__METHOD__.' is deprecated.', \E_USER_DEPRECATED);

        return $this->updateUser(
            Request\UpdateUser::new()
                ->withUid($uid)
                ->markAsEnabled()
        );
    }

    /**
     * @param mixed $uid
     *
     * @codeCoverageIgnore
     *
     * @deprecated 4.2.0
     * @see ApiClient::updateUser()
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function disableUser($uid): ResponseInterface
    {
        \trigger_error(__METHOD__.' is deprecated.', \E_USER_DEPRECATED);

        return $this->updateUser(
            Request\UpdateUser::new()
                ->withUid($uid)
                ->markAsDisabled()
        );
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function deleteUser(string $uid): ResponseInterface
    {
        return $this->requestApi('deleteAccount', [
            'localId' => $uid,
        ]);
    }

    /**
     * @deprecated 4.2.0
     * @see ApiClient::updateUser()
     *
     * @codeCoverageIgnore
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function changeUserPassword(string $uid, string $newPassword): ResponseInterface
    {
        \trigger_error(__METHOD__.' is deprecated.', \E_USER_DEPRECATED);

        return $this->updateUser(
            Request\UpdateUser::new()
                ->withUid($uid)
                ->withClearTextPassword($newPassword)
        );
    }

    /**
     * @deprecated 4.2.0
     * @see ApiClient::updateUser()
     *
     * @codeCoverageIgnore
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function changeUserEmail(string $uid, string $newEmail): ResponseInterface
    {
        \trigger_error(__METHOD__.' is deprecated.', \E_USER_DEPRECATED);

        return $this->updateUser(
            Request\UpdateUser::new()
                ->withUid($uid)
                ->withEmail($newEmail)
        );
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function getAccountInfo(string $uid): ResponseInterface
    {
        return $this->requestApi('getAccountInfo', [
            'localId' => [$uid],
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function verifyPassword(string $email, string $password): ResponseInterface
    {
        return $this->requestApi('verifyPassword', [
            'email' => $email,
            'password' => $password,
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function sendEmailVerification(string $idToken, string $continueUrl = null, string $locale = null): ResponseInterface
    {
        $headers = $locale ? ['X-Firebase-Locale' => $locale] : null;

        $data = \array_filter([
            'requestType' => 'VERIFY_EMAIL',
            'idToken' => $idToken,
            'continueUrl' => $continueUrl,
        ]);

        return $this->requestApi('getOobConfirmationCode', $data, $headers);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function sendPasswordResetEmail(string $email, string $continueUrl = null, string $locale = null): ResponseInterface
    {
        $headers = $locale ? ['X-Firebase-Locale' => $locale] : null;

        $data = \array_filter([
            'email' => $email,
            'requestType' => 'PASSWORD_RESET',
            'continueUrl' => $continueUrl,
        ]);

        return $this->requestApi('getOobConfirmationCode', $data, $headers);
    }

    /**
     * @param string $oobCode the email action code sent to the user's email for resetting the password
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    public function verifyPasswordResetCode(string $oobCode): ResponseInterface
    {
        return $this->requestApi('resetPassword', [
            'oobCode' => $oobCode,
        ]);
    }

    /**
     * @param string $oobCode the email action code sent to the user's email for resetting the password
     * @param mixed $newPassword the user's new password
     *
     * @throws FirebaseException
     */
    public function confirmPasswordReset(string $oobCode, $newPassword): ResponseInterface
    {
        return $this->requestApi('resetPassword', [
            'oobCode' => $oobCode,
            'newPassword' => $newPassword,
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function revokeRefreshTokens(string $uid): ResponseInterface
    {
        return $this->requestApi('setAccountInfo', [
            'localId' => $uid,
            'validSince' => \time(),
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function unlinkProvider(string $uid, array $providers): ResponseInterface
    {
        return $this->requestApi('setAccountInfo', [
            'localId' => $uid,
            'deleteProvider' => $providers,
        ]);
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function linkProviderThroughAccessToken(Provider $provider, string $accessToken): ResponseInterface
    {
        return $this->linkProvider($provider, $accessToken, 'access_token');
    }

    /**
     * @throws AuthException
     * @throws FirebaseException
     */
    public function linkProviderThroughIdToken(Provider $provider, string $idToken): ResponseInterface
    {
        return $this->linkProvider($provider, $idToken, 'id_token');
    }

    /**
     * Links the given OAuth credential (e.g. Google ID token, or Facebook access token, etc) to Firebase.
     * Basically logs in the user to Firebase, if the authentication provider is enabled for the project.
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    private function linkProvider(Provider $provider, string $token, string $tokenKeyName): ResponseInterface
    {
        return $this->requestApi('https://identitytoolkit.googleapis.com/v1/accounts:signInWithIdp', [
            'postBody' => \http_build_query([
                $tokenKeyName => $token,
                'providerId' => (string) $provider,
            ]),
            'returnSecureToken' => true,
            'returnIdpCredential' => true,
            'requestUri' => 'http://localhost', // this doesn't matter here, but required
        ]);
    }

    /**
     * @param mixed $data
     * @param array $headers
     *
     * @throws AuthException
     * @throws FirebaseException
     */
    private function requestApi(string $uri, $data, array $headers = null): ResponseInterface
    {
        if ($data instanceof \JsonSerializable && empty($data->jsonSerialize())) {
            $data = (object) []; // Will be '{}' instead of '[]' when JSON encoded
        }

        $options = \array_filter([
            'json' => $data,
            'headers' => $headers,
        ]);

        try {
            return $this->request('POST', $uri, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}
