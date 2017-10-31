<?php

namespace Kreait;

use Firebase\Auth\Token\Handler as TokenHandler;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;
use Kreait\Firebase\Auth\User;
use Kreait\Firebase\Database;
use Kreait\Firebase\Exception\LogicException;
use Kreait\Firebase\Http;
use Kreait\Firebase\ServiceAccount;
use Psr\Http\Message\UriInterface;

class Firebase
{
    /**
     * @var ServiceAccount
     */
    private $serviceAccount;

    /**
     * @var UriInterface
     */
    private $databaseUri;

    /**
     * @var Database
     */
    private $database;

    /**
     * @var TokenHandler
     */
    private $tokenHandler;

    /**
     * @var Firebase\Auth|null
     */
    private $auth;

    public function __construct(
        ServiceAccount $serviceAccount,
        UriInterface $databaseUri,
        TokenHandler $tokenHandler,
        Firebase\Auth $auth = null
    ) {
        $this->serviceAccount = $serviceAccount;
        $this->databaseUri = $databaseUri;
        $this->tokenHandler = $tokenHandler;
        $this->auth = $auth;
    }

    /**
     * Returns a new instance with a changed database URI.
     *
     * @param string|UriInterface $databaseUri
     *
     * @return Firebase
     */
    public function withDatabaseUri($databaseUri): Firebase
    {
        return new self($this->serviceAccount, Psr7\uri_for($databaseUri), $this->tokenHandler);
    }

    /**
     * Returns an instance of the realtime database.
     *
     * @return Database
     */
    public function getDatabase(): Database
    {
        if (!$this->database) {
            $this->database = $this->createDatabase();
        }

        return $this->database;
    }

    /**
     * Returns an Auth instance.
     *
     * @throws \Kreait\Firebase\Exception\LogicException
     *
     * @return Firebase\Auth
     */
    public function getAuth(): Firebase\Auth
    {
        if (!$this->auth) {
            throw new LogicException('You need to configure Firebase with an API key via the factory to use the Authentication capabilities.');
        }

        return $this->auth;
    }

    /**
     * Returns a new instance with the permissions
     * of the user with the given UID and claims.
     *
     * @deprecated 3.2 use {@see \Kreait\Firebase\Auth::getUser()} and {@see \Kreait\Firebase::asUser()} instead
     *
     * @param string|User $user
     * @param array $claims
     *
     * @return Firebase
     */
    public function asUserWithClaims($user, array $claims = []): Firebase
    {
        if ($user instanceof User) {
            $uid = $user->getUid();
        } else {
            $uid = (string) $user;
        }

        return $this->auth
            ? $this->withCustomAuth(new Http\Auth\UserAuth($this->auth->getUser($uid, $claims)))
            : $this->withCustomAuth(new Http\Auth\CustomToken($uid, $claims));
    }

    /**
     * Returns a new instance with the permissions of the given user.
     *
     * @param User $user
     *
     * @return Firebase
     */
    public function asUser(User $user): Firebase
    {
        return $this->withCustomAuth(new Http\Auth\UserAuth($user));
    }

    /**
     * @deprecated 3.2 use {@see \Kreait\Firebase\Auth::createCustomToken()} or {@see \Kreait\Firebase\Auth::verifyIdToken()} instead
     */
    public function getTokenHandler(): TokenHandler
    {
        trigger_error(
            'The token handler is deprecated and will be removed in release 4.0 of this library.'
            .' Use Firebase\Auth::createCustomToken() or Firebase\Auth::verifyIdToken() instead.',
            E_USER_DEPRECATED
        );

        return $this->tokenHandler;
    }

    private function withCustomAuth(Http\Auth $override): Firebase
    {
        $firebase = new self($this->serviceAccount, $this->databaseUri, $this->tokenHandler);
        $firebase->database = $this->createDatabase()->withCustomAuth($override);

        return $firebase;
    }

    private function createDatabase(): Database
    {
        $client = $this->createDatabaseClient($this->databaseUri);

        return new Database($this->databaseUri, $client);
    }

    private function createDatabaseClient(UriInterface $databaseUri): Database\ApiClient
    {
        $googleAuthTokenMiddleware = $this->createGoogleAuthTokenMiddleware($this->serviceAccount);

        $stack = HandlerStack::create();
        $stack->push(Http\Middleware::ensureJsonSuffix(), 'ensure_json_suffix');
        $stack->push($googleAuthTokenMiddleware, 'auth_service_account');

        $http = new Client([
            'base_uri' => $databaseUri,
            'handler' => $stack,
            'auth' => 'google_auth',
        ]);

        return new Database\ApiClient($http);
    }

    private function createGoogleAuthTokenMiddleware(ServiceAccount $serviceAccount): AuthTokenMiddleware
    {
        $scopes = [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/firebase.database',
        ];

        $credentials = [
            'client_email' => $serviceAccount->getClientEmail(),
            'client_id' => $serviceAccount->getClientId(),
            'private_key' => $serviceAccount->getPrivateKey(),
        ];

        return new AuthTokenMiddleware(new ServiceAccountCredentials($scopes, $credentials));
    }
}
