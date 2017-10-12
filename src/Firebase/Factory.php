<?php

namespace Kreait\Firebase;

use Firebase\Auth\Token\Handler as TokenHandler;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;
use Kreait\Firebase;
use Kreait\Firebase\Auth\CustomTokenGenerator;
use Kreait\Firebase\Auth\IdTokenVerifier;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\ServiceAccount\Discoverer;
use Psr\Http\Message\UriInterface;

final class Factory
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

    /**
     * @var string|null
     */
    private $apiKey;

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
            'This method is deprecated and will be removed in release 4.0 of this library.'
            .' Use Firebase\Factory::withServiceAccount() instead.', E_USER_DEPRECATED
        );

        return $this->withServiceAccount(ServiceAccount::fromValue($credentials));
    }

    public function withApiKey(string $apiKey): self
    {
        $factory = clone $this;
        $factory->apiKey = $apiKey;

        return $factory;
    }

    public function withServiceAccount(ServiceAccount $serviceAccount): self
    {
        $factory = clone $this;
        $factory->serviceAccount = $serviceAccount;

        return $factory;
    }

    public function withServiceAccountAndApiKey(ServiceAccount $serviceAccount, string $apiKey): self
    {
        $factory = clone $this;
        $factory->serviceAccount = $serviceAccount;
        $factory->apiKey = $apiKey;

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

    /**
     * @deprecated 3.2 Use `Kreait\Firebase\Auth::createCustomToken()` and `Kreait\Firebase\Auth::verifyIdToken()` instead.
     *
     * @param TokenHandler $handler
     *
     * @return Factory
     */
    public function withTokenHandler(TokenHandler $handler): self
    {
        trigger_error(
            'The token handler is deprecated and will be removed in release 4.0 of this library.'
            .' Use Firebase\Auth::createCustomToken() or Firebase\Auth::verifyIdToken() instead.',
            E_USER_DEPRECATED
        );

        $factory = clone $this;
        $factory->tokenHandler = $handler;

        return $factory;
    }

    public function create(): Firebase
    {
        $serviceAccount = $this->serviceAccount ?? $this->getServiceAccountDiscoverer()->discover();
        $databaseUri = $this->databaseUri ?? $this->getDatabaseUriFromServiceAccount($serviceAccount);
        $tokenHandler = $this->tokenHandler ?? $this->getDefaultTokenHandler($serviceAccount);
        $tokenGenerator = new CustomTokenGenerator($serviceAccount);
        $idTokenVerifier = new IdTokenVerifier($serviceAccount);
        $auth = $this->apiKey ? $this->createAuth($this->apiKey, $tokenGenerator, $idTokenVerifier) : null;

        return new Firebase($serviceAccount, $databaseUri, $tokenHandler, $auth);
    }

    private function getServiceAccountDiscoverer(): Discoverer
    {
        return $this->serviceAccountDiscoverer ?? new Discoverer();
    }

    private function getDatabaseUriFromServiceAccount(ServiceAccount $serviceAccount): UriInterface
    {
        return Psr7\uri_for(sprintf(self::$databaseUriPattern, $serviceAccount->getProjectId()));
    }

    private function getDefaultTokenHandler(ServiceAccount $serviceAccount): TokenHandler
    {
        return new TokenHandler(
            $serviceAccount->getProjectId(),
            $serviceAccount->getClientEmail(),
            $serviceAccount->getPrivateKey()
        );
    }

    private function createAuth(string $apiKey, CustomTokenGenerator $customTokenGenerator, IdTokenVerifier $idTokenVerifier): Auth
    {
        $client = $this->createAuthApiClient($apiKey);

        return new Auth($client, $customTokenGenerator, $idTokenVerifier);
    }

    private function createAuthApiClient(string $apiKey): Firebase\Auth\ApiClient
    {
        $stack = HandlerStack::create();
        $stack->push(Middleware::ensureApiKey($apiKey), 'ensure_api_key');

        $httpClient = new Client([
            'base_uri' => 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/',
            'handler' => $stack,
        ]);

        return new Firebase\Auth\ApiClient($httpClient);
    }
}
