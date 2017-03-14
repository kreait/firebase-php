<?php

namespace Firebase;

use Firebase\Exception\InvalidArgumentException;
use Firebase\Exception\LogicException;
use Firebase\V3\Firebase;
use Google\Auth\CredentialsLoader;
use GuzzleHttp\Psr7;
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

    private function setupDefaults()
    {
        $this->credentialPaths = array_filter([
            getenv(self::ENV_VAR),
            getenv(CredentialsLoader::ENV_VAR),
        ]);
    }

    public function create()
    {
        $serviceAccount = $this->getServiceAccount();
        $databaseUri = $this->databaseUri ?? $this->getDatabaseUriFromServiceAccount($serviceAccount);

        return new Firebase($serviceAccount, $databaseUri);
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

        throw new LogicException(sprintf(
            'No service account has been found. Please set the path to a service account credentials file with %s::%s()',
            static::class, 'withCredentials($path)'
        ));
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
}
