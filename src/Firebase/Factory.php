<?php

namespace Kreait\Firebase;

use Firebase\Auth\Token\Handler as TokenHandler;
use GuzzleHttp\Psr7;
use Kreait\Firebase;
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

    private static $databaseUriPattern = 'https://%s.firebaseio.com';

    /**
     * @deprecated 3.1 use {@see withServiceAccount()} instead
     * @codeCoverageIgnore
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

    public function withTokenHandler(TokenHandler $handler): self
    {
        $factory = clone $this;
        $factory->tokenHandler = $handler;

        return $factory;
    }

    public function create(): Firebase
    {
        $serviceAccount = $this->serviceAccount ?? $this->getServiceAccountDiscoverer()->discover();
        $databaseUri = $this->databaseUri ?? $this->getDatabaseUriFromServiceAccount($serviceAccount);
        $tokenHandler = $this->tokenHandler ?? $this->getDefaultTokenHandler($serviceAccount);

        return new Firebase($serviceAccount, $databaseUri, $tokenHandler);
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
}
