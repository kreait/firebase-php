<?php

namespace Kreait\Firebase;

use Firebase\Auth\Token\Handler as TokenHandler;
use Google\Auth\CredentialsLoader;
use GuzzleHttp\Psr7;
use Kreait\Firebase;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\LogicException;
use Psr\Http\Message\UriInterface;

final class Factory
{
    const ENV_VAR = 'FIREBASE_CREDENTIALS';

    /**
     * @var string[]
     */
    private $credentialPaths;

    /**
     * @var UriInterface
     */
    private $databaseUri;

    /**
     * @var TokenHandler
     */
    private $tokenHandler;

    private static $databaseUriPattern = 'https://%s.firebaseio.com';

    public function __construct()
    {
        $this->setupDefaults();
    }

    public function withCredentials(string $path): self
    {
        $factory = clone $this;
        array_unshift($factory->credentialPaths, $path);

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

    private function setupDefaults()
    {
        $this->credentialPaths = array_filter([
            getenv(self::ENV_VAR),
            getenv(CredentialsLoader::ENV_VAR),
        ]);
    }

    public function create(): Firebase
    {
        $serviceAccount = $this->getServiceAccount();

        return new Firebase(
            $serviceAccount,
            $this->databaseUri ?? $this->getDatabaseUriFromServiceAccount($serviceAccount),
            $this->tokenHandler ?? $this->getDefaultTokenHandler($serviceAccount)
        );
    }

    private function getDatabaseUriFromServiceAccount(ServiceAccount $serviceAccount): UriInterface
    {
        return Psr7\uri_for(sprintf(self::$databaseUriPattern, $serviceAccount->getProjectId()));
    }

    private function getServiceAccount(): ServiceAccount
    {
        if (count($serviceAccounts = $this->getServiceAccountCandidates())) {
            return reset($serviceAccounts);
        }

        // @codeCoverageIgnoreStart
        if ($credentials = CredentialsLoader::fromWellKnownFile()) {
            return ServiceAccount::fromValue($credentials);
        }
        // @codeCoverageIgnoreEnd

        throw new Firebase\Exception\CredentialsNotFound($this->credentialPaths);
    }

    private function getServiceAccountCandidates(): array
    {
        return array_filter(array_map(function ($path) {
            try {
                return ServiceAccount::fromValue($path);
            } catch (InvalidArgumentException $e) {
                return null;
            }
        }, $this->credentialPaths));
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
