<?php

namespace Kreait;

use Firebase\Auth\Token\Handler as TokenHandler;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;
use Kreait\Firebase\Database;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Http\Auth;
use Kreait\Firebase\Http\Auth\CustomToken;
use Kreait\Firebase\Http\Middleware;
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

    public function __construct(ServiceAccount $serviceAccount, UriInterface $databaseUri, TokenHandler $tokenHandler)
    {
        $this->serviceAccount = $serviceAccount;
        $this->databaseUri = $databaseUri;
        $this->tokenHandler = $tokenHandler;
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
     * Returns a new instance with the permissions
     * of the user with the given UID and claims.
     *
     * @param string $uid
     * @param array $claims
     *
     * @return Firebase
     */
    public function asUserWithClaims(string $uid, array $claims = []): Firebase
    {
        return $this->withCustomAuth(new CustomToken($uid, $claims));
    }

    /**
     * Returns a Token Handler to be used for creating Custom Tokens and
     * verifying ID tokens.
     *
     * @see https://firebase.google.com/docs/auth/admin/create-custom-tokens
     * @see https://firebase.google.com/docs/auth/admin/verify-id-tokens
     *
     * @return TokenHandler
     */
    public function getTokenHandler(): TokenHandler
    {
        return $this->tokenHandler;
    }

    private function withCustomAuth(Auth $override): Firebase
    {
        $firebase = new self($this->serviceAccount, $this->databaseUri, $this->tokenHandler);
        $firebase->database = $this->getDatabase()->withCustomAuth($override);

        return $firebase;
    }

    private function createDatabase(): Database
    {
        $client = $this->createDatabaseClient($this->databaseUri);

        return new Database($this->databaseUri, $client);
    }

    private function createDatabaseClient(UriInterface $databaseUri): ApiClient
    {
        $googleAuthTokenMiddleware = $this->createGoogleAuthTokenMiddleware($this->serviceAccount);

        $stack = HandlerStack::create();
        $stack->push(Middleware::ensureJson(), 'ensure_json');
        $stack->push($googleAuthTokenMiddleware, 'auth_service_account');

        $http = new Client([
            'base_uri' => $databaseUri,
            'handler' => $stack,
            'auth' => 'google_auth',
        ]);

        return new ApiClient($http);
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
