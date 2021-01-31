<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Contract\AuthProvider;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Contract\DatabaseProvider;
use Kreait\Firebase\Contract\DynamicLinks;
use Kreait\Firebase\Contract\DynamicLinksProvider;
use Kreait\Firebase\Contract\Firestore;
use Kreait\Firebase\Contract\FirestoreProvider;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Contract\MessagingProvider;
use Kreait\Firebase\Contract\RemoteConfig;
use Kreait\Firebase\Contract\RemoteConfigProvider;
use Kreait\Firebase\Contract\Storage;
use Kreait\Firebase\Contract\StorageProvider;
use Kreait\Firebase\Exception\FirebaseError;
use Kreait\Firebase\Project\Config;

final class Project implements AuthProvider, DatabaseProvider, DynamicLinksProvider, FirestoreProvider, MessagingProvider, RemoteConfigProvider, StorageProvider
{
    /** @var Config */
    private $config;

    /** @var Factory */
    private $baseFactory;

    /** @var Factory|null */
    private $configuredFactory;

    /** @var array<string, mixed> */
    private $cache = [];

    /**
     * @internal
     */
    public function __construct(Config $config, Factory $factory)
    {
        $this->config = $config;
        $this->baseFactory = $factory;
    }

    public function auth(): Auth
    {
        return $this->factory()->createAuth();
    }

    public function database(?string $url = null): Database
    {
        $url = $url ?? $this->config->defaultDatabaseUrl();

        if (!$url) {
            throw new FirebaseError('No database URL was given and no default database has been configured.');
        }

        $key = 'database_'.\base64_encode($url);

        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $this->factory()->withDatabaseUri($url)->createDatabase();
        }

        return $this->cache[$key];
    }

    public function dynamicLinks(?string $domain = null): DynamicLinks
    {
        $domain = $domain ?? $this->config->defaultDynamicLinksDomain();

        if (!$domain) {
            throw new FirebaseError('No dynamic links domain was given and no default domain has been configured.');
        }

        $key = 'dynamic_links_'.\base64_encode($domain);

        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $this->factory()->createDynamicLinksService($domain);
        }

        return $this->cache[$key];
    }

    public function firestore(): Firestore
    {
        if (!isset($this->cache['firestore'])) {
            $this->cache['firestore'] = $this->factory()->createFirestore();
        }

        return $this->cache['firestore'];
    }

    public function messaging(): Messaging
    {
        if (!isset($this->cache['messaging'])) {
            $this->cache['messaging'] = $this->factory()->createMessaging();
        }

        return $this->cache['messaging'];
    }

    public function remoteConfig(): RemoteConfig
    {
        if (!isset($this->cache['remote_config'])) {
            $this->cache['remote_config'] = $this->factory()->createRemoteConfig();
        }

        return $this->cache['remote_config'];
    }

    public function storage(): Storage
    {
        if (!isset($this->cache['storage'])) {
            $this->cache['storage'] = $this->factory()->createStorage();
        }

        return $this->cache['storage'];
    }

    private function factory(): Factory
    {
        if (!$this->configuredFactory) {
            $this->configuredFactory = $this->buildFactory();
        }

        return $this->configuredFactory;
    }

    private function buildFactory(): Factory
    {
        $factory = $this->baseFactory;
        $config = $this->config;

        if ($serviceAccount = $config->serviceAccount()) {
            $factory = $factory->withServiceAccount($serviceAccount);
        }

        return $factory;
    }
}
