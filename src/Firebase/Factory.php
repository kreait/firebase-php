<?php

namespace Kreait\Firebase;

use Firebase\Auth\Token\Verifier as BaseVerifier;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;
use Kreait\Firebase;
use Kreait\Firebase\Auth\CustomTokenGenerator;
use Kreait\Firebase\Auth\IdTokenVerifier;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\ServiceAccount\Discoverer;
use Psr\Http\Message\UriInterface;

class Factory
{
    /**
     * @var UriInterface
     */
    private $databaseUri;

    /**
     * @var ServiceAccount
     */
    private $serviceAccount;

    /**
     * @var Discoverer
     */
    private $serviceAccountDiscoverer;

    /**
     * @var string|null
     */
    private $uid;

    /**
     * @var array
     */
    private $claims = [];

    private static $databaseUriPattern = 'https://%s.firebaseio.com';

    public function withServiceAccount(ServiceAccount $serviceAccount): self
    {
        $factory = clone $this;
        $factory->serviceAccount = $serviceAccount;

        return $factory;
    }

    public function withServiceAccountDiscoverer(Discoverer $discoverer): self
    {
        $factory = clone $this;
        $factory->serviceAccountDiscoverer = $discoverer;

        return $factory;
    }

    public function withDatabaseUri($uri): self
    {
        $factory = clone $this;
        $factory->databaseUri = Psr7\uri_for($uri);

        return $factory;
    }

    public function asUser(string $uid, array $claims = []): self
    {
        $factory = clone $this;
        $factory->uid = $uid;
        $factory->claims = $claims;

        return $factory;
    }

    public function create(): Firebase
    {
        $database = $this->createDatabase();
        $auth = $this->createAuth();

        return new Firebase($database, $auth);
    }

    private function getServiceAccountDiscoverer(): Discoverer
    {
        return $this->serviceAccountDiscoverer ?? new Discoverer();
    }

    private function getServiceAccount(): ServiceAccount
    {
        if (!$this->serviceAccount) {
            $this->serviceAccount = $this->getServiceAccountDiscoverer()->discover();
        }

        return $this->serviceAccount;
    }

    private function getDatabaseUri(): UriInterface
    {
        return $this->databaseUri ?: $this->getDatabaseUriFromServiceAccount($this->getServiceAccount());
    }

    private function getDatabaseUriFromServiceAccount(ServiceAccount $serviceAccount): UriInterface
    {
        return Psr7\uri_for(sprintf(self::$databaseUriPattern, $serviceAccount->getProjectId()));
    }

    private function getCustomTokenGenerator(): CustomTokenGenerator
    {
        return new CustomTokenGenerator($this->getServiceAccount());
    }

    private function getIdTokenVerifier(): IdTokenVerifier
    {
        return new IdTokenVerifier(new BaseVerifier($this->getServiceAccount()->getProjectId()));
    }

    private function createAuth(): Auth
    {
        $http = $this->createApiClient($this->getServiceAccount(), [
            'base_uri' => 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/',
        ]);

        $apiClient = new Auth\ApiClient($http);

        return new Auth($apiClient, $this->getCustomTokenGenerator(), $this->getIdTokenVerifier());
    }

    private function createDatabase(): Database
    {
        $http = $this->createApiClient($this->getServiceAccount());

        $middlewares = [
            'json_suffix' => Firebase\Http\Middleware::ensureJsonSuffix(),
        ];

        if ($this->uid) {
            $authOverride = new Http\Auth\CustomToken($this->uid, $this->claims);

            $middlewares['auth_override'] = Middleware::overrideAuth($authOverride);
        }

        /** @var HandlerStack $handler */
        $handler = $http->getConfig('handler');

        foreach ($middlewares as $name => $middleware) {
            $handler->push($middleware, $name);
        }

        return new Database($this->getDatabaseUri(), new Database\ApiClient($http));
    }

    private function createApiClient(ServiceAccount $serviceAccount, array $config = []): Client
    {
        $googleAuthTokenMiddleware = $this->createGoogleAuthTokenMiddleware($serviceAccount);

        $stack = HandlerStack::create();
        $stack->push($googleAuthTokenMiddleware, 'auth_service_account');

        $config = array_merge($config, [
            'handler' => $stack,
            'auth' => 'google_auth',
        ]);

        return new Client($config);
    }

    private function createGoogleAuthTokenMiddleware(ServiceAccount $serviceAccount): AuthTokenMiddleware
    {
        $scopes = [
            'https://www.googleapis.com/auth/cloud-platform',
            'https://www.googleapis.com/auth/firebase',
            'https://www.googleapis.com/auth/userinfo.email',
        ];

        $credentials = [
            'client_email' => $serviceAccount->getClientEmail(),
            'client_id' => $serviceAccount->getClientId(),
            'private_key' => $serviceAccount->getPrivateKey(),
        ];

        return new AuthTokenMiddleware(new ServiceAccountCredentials($scopes, $credentials));
    }
}
