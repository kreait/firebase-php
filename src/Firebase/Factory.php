<?php

namespace Kreait\Firebase;

use Firebase\Auth\Token\Handler as TokenHandler;
use Firebase\Auth\Token\Verifier as BaseVerifier;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;
use Kreait\Firebase;
use Kreait\Firebase\Auth\CustomTokenGenerator;
use Kreait\Firebase\Auth\IdTokenVerifier;
use Kreait\Firebase\ServiceAccount\Discoverer;
use Psr\Http\Message\UriInterface;

class Factory
{
    /**
     * @var UriInterface
     */
    private $databaseUri;

    /**
     * @var TokenHandler
     */
    private $tokenHandler;

    /**
     * @var ServiceAccount
     */
    private $serviceAccount;

    /**
     * @var Discoverer
     */
    private $serviceAccountDiscoverer;

    private static $databaseUriPattern = 'https://%s.firebaseio.com';

    /**
     * @deprecated 3.1 use {@see withServiceAccount()} instead
     *
     * @param string $credentials Path to a credentials file
     *
     * @throws \Kreait\Firebase\Exception\InvalidArgumentException
     *
     * @return self
     */
    public function withCredentials(string $credentials): self
    {
        trigger_error(
            'This method is deprecated and will be removed in the next major release.'
            .' Use Firebase\Factory::withServiceAccount() instead.', E_USER_DEPRECATED
        );

        return $this->withServiceAccount(ServiceAccount::fromValue($credentials));
    }

    /**
     * @deprecated 3.8
     *
     * @return self
     */
    public function withApiKey(): self
    {
        trigger_error(
            'This method is deprecated and will be removed in the next major release.',
            E_USER_DEPRECATED
        );

        return $this;
    }

    public function withServiceAccount(ServiceAccount $serviceAccount): self
    {
        $factory = clone $this;
        $factory->serviceAccount = $serviceAccount;

        return $factory;
    }

    /**
     * @deprecated 3.8 The api key is not required anymore, use {@see withServiceAccount()} instead
     *
     * @param ServiceAccount $serviceAccount
     *
     * @return Factory
     */
    public function withServiceAccountAndApiKey(ServiceAccount $serviceAccount): self
    {
        trigger_error(
            'The api key is not required anymore.'
            .' This method is deprecated and will be removed in the next major release.'
            .' Use Kreait\Firebase\Factory::withServiceAccount() instead.', E_USER_DEPRECATED
        );

        return $this->withServiceAccount($serviceAccount);
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

    /**
     * @deprecated 3.2 Use `Kreait\Firebase\Auth::createCustomToken()` and `Kreait\Firebase\Auth::verifyIdToken()` instead.
     *
     * @param TokenHandler $handler
     *
     * @return Factory
     */
    public function withTokenHandler(TokenHandler $handler = null): self
    {
        $factory = clone $this;
        $factory->tokenHandler = $handler;

        return $factory;
    }

    public function create(): Firebase
    {
        $serviceAccount = $this->getServiceAccount();
        $databaseUri = $this->databaseUri ?? $this->getDatabaseUriFromServiceAccount($serviceAccount);

        return new Firebase($serviceAccount, $databaseUri);
    }

    private function getServiceAccountDiscoverer(): Discoverer
    {
        return $this->serviceAccountDiscoverer ?? new Discoverer();
    }

    public function getServiceAccount(): ServiceAccount
    {
        return $this->serviceAccount ?: $this->getServiceAccountDiscoverer()->discover();
    }

    public function getDatabaseUri(): UriInterface
    {
        return $this->databaseUri ?: $this->getDatabaseUriFromServiceAccount($this->getServiceAccount());
    }

    private function getDatabaseUriFromServiceAccount(ServiceAccount $serviceAccount): UriInterface
    {
        return Psr7\uri_for(sprintf(self::$databaseUriPattern, $serviceAccount->getProjectId()));
    }

    public function getCustomTokenGenerator(): CustomTokenGenerator
    {
        return new CustomTokenGenerator($this->getServiceAccount());
    }

    public function getIdTokenVerifier(): IdTokenVerifier
    {
        return new IdTokenVerifier(new BaseVerifier($this->getServiceAccount()->getProjectId()));
    }

    public function getTokenHandler(): TokenHandler
    {
        if (!$this->tokenHandler) {
            $this->tokenHandler = $this->createDefaultTokenHandler($this->getServiceAccount());
        }

        return $this->tokenHandler;
    }

    private function createDefaultTokenHandler(ServiceAccount $serviceAccount): TokenHandler
    {
        return new TokenHandler(
            $serviceAccount->getProjectId(),
            $serviceAccount->getClientEmail(),
            $serviceAccount->getPrivateKey()
        );
    }

    public function createAuth(): Auth
    {
        $http = $this->createApiClient($this->getServiceAccount(), [
            'base_uri' => 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/',
        ]);

        $apiClient = new Auth\ApiClient($http);

        return new Auth($apiClient, $this->getCustomTokenGenerator(), $this->getIdTokenVerifier());
    }

    public function createDatabase(): Database
    {
        $http = $this->createApiClient($this->getServiceAccount());
        $http->getConfig('handler')
            ->push(Firebase\Http\Middleware::ensureJsonSuffix(), 'json_suffix');

        $apiClient = new Database\ApiClient($http);

        return new Database($this->getDatabaseUri(), $apiClient);
    }

    private function createApiClient(ServiceAccount $serviceAccount, array $config = []): Client
    {
        $googleAuthTokenMiddleware = self::createGoogleAuthTokenMiddleware($serviceAccount);

        $stack = HandlerStack::create();
        $stack->push($googleAuthTokenMiddleware, 'auth_service_account');

        $config = array_merge($config, [
            'handler' => $stack,
            'auth' => 'google_auth',
        ]);

        return new Client($config);
    }

    public static function createGoogleAuthTokenMiddleware(ServiceAccount $serviceAccount): AuthTokenMiddleware
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
