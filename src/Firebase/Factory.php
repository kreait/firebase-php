<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Firebase\Auth\Token\Cache\InMemoryCache;
use Firebase\Auth\Token\Domain\Generator;
use Firebase\Auth\Token\Generator as CustomTokenGenerator;
use Firebase\Auth\Token\HttpKeyStore;
use Firebase\Auth\Token\Verifier;
use Google\Auth\Credentials\GCECredentials;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Storage\StorageClient;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use function GuzzleHttp\Psr7\uri_for;
use Kreait\Clock;
use Kreait\Clock\SystemClock;
use Kreait\Firebase;
use Kreait\Firebase\Auth\CustomTokenViaGoogleIam;
use Kreait\Firebase\Exception\LogicException;
use Kreait\Firebase\Exception\RuntimeException;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\ServiceAccount\Discoverer;
use Kreait\Firebase\Value\Url;
use Kreait\GcpMetadata;
use Psr\Http\Message\UriInterface;
use Psr\SimpleCache\CacheInterface;
use Throwable;

class Factory
{
    const API_CLIENT_SCOPES = [
        'https://www.googleapis.com/auth/iam',
        'https://www.googleapis.com/auth/cloud-platform',
        'https://www.googleapis.com/auth/firebase',
        'https://www.googleapis.com/auth/firebase.database',
        'https://www.googleapis.com/auth/firebase.messaging',
        'https://www.googleapis.com/auth/firebase.remoteconfig',
        'https://www.googleapis.com/auth/userinfo.email',
    ];

    /**
     * @var UriInterface|null
     */
    protected $databaseUri;

    /**
     * @var string|null
     */
    protected $defaultStorageBucket;

    /**
     * @var ServiceAccount|null
     */
    protected $serviceAccount;

    /**
     * @var Discoverer|null
     */
    protected $serviceAccountDiscoverer;

    /**
     * @var string|null
     */
    protected $uid;

    /**
     * @var array
     */
    protected $claims = [];

    /**
     * @var CacheInterface|null
     */
    protected $verifierCache;

    /**
     * @var array
     */
    protected $httpClientConfig = [];

    /**
     * @var array
     */
    protected $httpClientMiddlewares = [];

    protected static $databaseUriPattern = 'https://%s.firebaseio.com';

    protected static $storageBucketNamePattern = '%s.appspot.com';

    /** @var Clock */
    protected $clock;

    public function __construct()
    {
        $this->serviceAccountDiscoverer = new Discoverer();
        $this->clock = new SystemClock();
    }

    public function withServiceAccount($serviceAccount): self
    {
        $serviceAccount = ServiceAccount::fromValue($serviceAccount);

        $factory = clone $this;
        $factory->serviceAccount = $serviceAccount;

        return $factory->withDisabledAutoDiscovery();
    }

    public function withServiceAccountDiscoverer(Discoverer $discoverer): self
    {
        $factory = clone $this;
        $factory->serviceAccountDiscoverer = $discoverer;

        return $factory;
    }

    public function withDisabledAutoDiscovery(): self
    {
        $factory = clone $this;
        $factory->serviceAccountDiscoverer = null;

        return $factory;
    }

    public function withDatabaseUri($uri): self
    {
        $factory = clone $this;
        $factory->databaseUri = uri_for($uri);

        return $factory;
    }

    public function withDefaultStorageBucket($name): self
    {
        $factory = clone $this;
        $factory->defaultStorageBucket = $name;

        return $factory;
    }

    public function withVerifierCache(CacheInterface $cache): self
    {
        $factory = clone $this;
        $factory->verifierCache = $cache;

        return $factory;
    }

    public function withHttpClientConfig(array $config = null): self
    {
        $factory = clone $this;
        $factory->httpClientConfig = $config ?? [];

        return $factory;
    }

    /**
     * @param callable[]|null $middlewares
     */
    public function withHttpClientMiddlewares(array $middlewares = null): self
    {
        $factory = clone $this;
        $factory->httpClientMiddlewares = $middlewares ?? [];

        return $factory;
    }

    public function withClock(Clock $clock): self
    {
        $factory = clone $this;
        $factory->clock = $clock;

        return $factory;
    }

    public function asUser(string $uid, array $claims = null): self
    {
        $factory = clone $this;
        $factory->uid = $uid;
        $factory->claims = $claims ?? [];

        return $factory;
    }

    /**
     * @deprecated 4.33 Use the component-specific create*() methods instead.
     * @see createAuth()
     * @see createDatabase()
     * @see createFirestore()
     * @see createMessaging()
     * @see createRemoteConfig()
     * @see createStorage()
     */
    public function create(): Firebase
    {
        /* @noinspection PhpInternalEntityUsedInspection */
        return new Firebase($this);
    }

    protected function getServiceAccount(): ServiceAccount
    {
        if (!$this->serviceAccount && $this->serviceAccountDiscoverer) {
            $this->serviceAccount = $this->serviceAccountDiscoverer->discover();
        }

        if (!$this->serviceAccount) {
            throw new LogicException('No service account has been configured.');
        }

        return $this->serviceAccount;
    }

    protected function getDatabaseUri(): UriInterface
    {
        return $this->databaseUri ?: $this->getDatabaseUriFromServiceAccount($this->getServiceAccount());
    }

    protected function getStorageBucketName(): string
    {
        return $this->defaultStorageBucket ?: $this->getStorageBucketNameFromServiceAccount($this->getServiceAccount());
    }

    protected function getDatabaseUriFromServiceAccount(ServiceAccount $serviceAccount): UriInterface
    {
        return uri_for(\sprintf(self::$databaseUriPattern, $serviceAccount->getSanitizedProjectId()));
    }

    protected function getStorageBucketNameFromServiceAccount(ServiceAccount $serviceAccount): string
    {
        return \sprintf(self::$storageBucketNamePattern, $serviceAccount->getSanitizedProjectId());
    }

    public function createAuth(): Auth
    {
        $http = $this->createApiClient([
            'base_uri' => 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/',
        ]);
        $apiClient = new Auth\ApiClient($http);

        $serviceAccount = $this->getServiceAccount();

        $customTokenGenerator = $this->createCustomTokenGenerator();
        $keyStore = new HttpKeyStore(new Client(), $this->verifierCache ?: new InMemoryCache());
        $baseVerifier = new Verifier($serviceAccount->getSanitizedProjectId(), $keyStore);
        $idTokenVerifier = new Firebase\Auth\IdTokenVerifier($baseVerifier, $this->clock);

        return new Auth($apiClient, $customTokenGenerator, $idTokenVerifier);
    }

    public function createCustomTokenGenerator(): Generator
    {
        $serviceAccount = $this->getServiceAccount();

        if ($serviceAccount->hasPrivateKey()) {
            return new CustomTokenGenerator($serviceAccount->getClientEmail(), $serviceAccount->getPrivateKey());
        }

        return new CustomTokenViaGoogleIam($serviceAccount->getClientEmail(), $this->createApiClient());
    }

    public function createDatabase(): Database
    {
        $http = $this->createApiClient();

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

    public function createRemoteConfig(): RemoteConfig
    {
        $http = $this->createApiClient([
            'base_uri' => 'https://firebaseremoteconfig.googleapis.com/v1/projects/'.$this->getServiceAccount()->getSanitizedProjectId().'/remoteConfig',
        ]);

        return new RemoteConfig(new RemoteConfig\ApiClient($http));
    }

    public function createMessaging(): Messaging
    {
        $projectId = $this->getServiceAccount()->getSanitizedProjectId();

        $messagingApiClient = new Messaging\ApiClient(
            $this->createApiClient([
                'base_uri' => 'https://fcm.googleapis.com/v1/projects/'.$projectId,
            ])
        );

        $appInstanceApiClient = new Messaging\AppInstanceApiClient(
            $this->createApiClient([
                'base_uri' => 'https://iid.googleapis.com',
                'headers' => [
                    'access_token_auth' => 'true',
                ],
            ])
        );

        return new Messaging($messagingApiClient, $appInstanceApiClient, $projectId);
    }

    /**
     * @param string|Url|UriInterface|mixed $defaultDynamicLinksDomain
     */
    public function createDynamicLinksService($defaultDynamicLinksDomain = null): DynamicLinks
    {
        $apiClient = $this->createApiClient();

        if ($defaultDynamicLinksDomain) {
            return DynamicLinks::withApiClientAndDefaultDomain($apiClient, $defaultDynamicLinksDomain);
        }

        return DynamicLinks::withApiClient($apiClient);
    }

    public function createFirestore(array $firestoreClientConfig = null): Firestore
    {
        $client = $this->createFirestoreClient($firestoreClientConfig);

        return Firestore::withFirestoreClient($client);
    }

    private function createFirestoreClient(array $config = null): FirestoreClient
    {
        $config = $config ?: [];
        $config = \array_merge($this->googleClientAuthConfig(), $config);

        try {
            return new FirestoreClient($config);
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to create a FirestoreClient: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    public function createStorage(array $storageClientConfig = null): Storage
    {
        $storageClientConfig = $storageClientConfig ?: [];

        $client = $this->createStorageClient($storageClientConfig);

        return new Storage($client, $this->getStorageBucketName());
    }

    private function createStorageClient(array $config = null): StorageClient
    {
        $config = $config ?: [];
        $config = \array_merge($this->googleClientAuthConfig(), $config);

        try {
            return new StorageClient($config);
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to create a StorageClient: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    public function createApiClient(array $config = null): Client
    {
        $config = $config ?? [];
        // If present, the config given to this method override fields passed to withHttpClientConfig()
        $config = \array_merge($this->httpClientConfig, $config);

        $googleAuthTokenMiddleware = $this->createGoogleAuthTokenMiddleware();

        $handler = $config['handler'] ?? null;

        if (!($handler instanceof HandlerStack)) {
            $handler = HandlerStack::create($handler);
        }

        foreach ($this->httpClientMiddlewares as $middleware) {
            $handler->push($middleware);
        }

        $handler->push($googleAuthTokenMiddleware);
        $handler->push(Middleware::responseWithSubResponses());

        $config['handler'] = $handler;
        $config['auth'] = 'google_auth';

        return new Client($config);
    }

    protected function createGoogleAuthTokenMiddleware(): AuthTokenMiddleware
    {
        $serviceAccount = $this->getServiceAccount();

        if ($serviceAccount->hasClientId() && $serviceAccount->hasPrivateKey()) {
            $credentials = new ServiceAccountCredentials(self::API_CLIENT_SCOPES, [
                'client_email' => $serviceAccount->getClientEmail(),
                'client_id' => $serviceAccount->getClientId(),
                'private_key' => $serviceAccount->getPrivateKey(),
            ]);
        } elseif ((new GcpMetadata())->isAvailable()) {
            // @codeCoverageIgnoreStart
            // We can't test this programatically when not on GCE/GCP
            $credentials = new GCECredentials();
        // @codeCoverageIgnoreEnd
        } else {
            throw new RuntimeException('Unable to determine credentials.');
        }

        return new AuthTokenMiddleware($credentials);
    }

    protected function googleClientAuthConfig(): array
    {
        try {
            $serviceAccount = $this->getServiceAccount();
        } catch (LogicException $e) {
            $serviceAccount = null;
        }

        $config = [];

        if ($serviceAccount && $filePath = $serviceAccount->getFilePath()) {
            $config['keyFilePath'] = $filePath;
        } elseif ($serviceAccount && $serviceAccount->hasClientId() && $serviceAccount->hasPrivateKey()) {
            $config['keyFile'] = \array_filter([
                'client_email' => $serviceAccount->getClientEmail(),
                'client_id' => $serviceAccount->getClientId(),
                'private_key' => $serviceAccount->getPrivateKey(),
                'project_id' => $serviceAccount->getProjectId(),
                'type' => 'service_account',
            ]);
        }

        return $config;
    }
}
