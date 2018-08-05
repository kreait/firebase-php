<?php

namespace Kreait\Firebase;

use Firebase\Auth\Token\Cache\InMemoryCache;
use Firebase\Auth\Token\Domain\Generator;
use Firebase\Auth\Token\Generator as CustomTokenGenerator;
use Firebase\Auth\Token\HttpKeyStore;
use Firebase\Auth\Token\Verifier;
use Google\Auth\Credentials\GCECredentials;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use Google\Cloud\Core\ServiceBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Kreait\Firebase;
use Kreait\Firebase\Auth\CustomTokenViaGoogleIam;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\ServiceAccount\Discoverer;
use Kreait\GcpMetadata;
use Psr\Http\Message\UriInterface;
use function GuzzleHttp\Psr7\uri_for;

class Factory
{
    /**
     * @var UriInterface
     */
    protected $databaseUri;

    /**
     * @var string
     */
    protected $defaultStorageBucket;

    /**
     * @var ServiceAccount
     */
    protected $serviceAccount;

    /**
     * @var Discoverer
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
     * @var \Psr\SimpleCache\CacheInterface
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
        $factory->databaseUri = uri_for($uri);

        return $factory;
    }

    public function withDefaultStorageBucket($name): self
    {
        $factory = clone $this;
        $factory->defaultStorageBucket = $name;

        return $factory;
    }

    /**
     * @param \Psr\SimpleCache\CacheInterface $cache
     *
     * @throws \Kreait\Firebase\Exception\InvalidArgumentException
     *
     * @return self
     */
    public function withVerifierCache($cache): self
    {
        if (!is_a($cache, $expected = 'Psr\SimpleCache\CacheInterface')) {
            throw new InvalidArgumentException('The verififier cache must be an instance of '.$expected);
        }

        $factory = clone $this;
        $factory->verifierCache = $cache;

        return $factory;
    }

    public function withHttpClientConfig(array $config): self
    {
        $factory = clone $this;
        $factory->httpClientConfig = $config;

        return $factory;
    }

    public function withHttpClientMiddlewares(array $middlewares): self
    {
        $factory = clone $this;
        $factory->httpClientMiddlewares = $middlewares;

        return $factory;
    }

    public function asUser(string $uid, array $claims = null): self
    {
        $factory = clone $this;
        $factory->uid = $uid;
        $factory->claims = $claims ?? [];

        return $factory;
    }

    public function create(): Firebase
    {
        $database = $this->createDatabase();
        $auth = $this->createAuth();
        $storage = $this->createStorage();
        $remoteConfig = $this->createRemoteConfig();
        $messaging = $this->createMessaging();

        return new Firebase($database, $auth, $storage, $remoteConfig, $messaging);
    }

    protected function getServiceAccountDiscoverer(): Discoverer
    {
        return $this->serviceAccountDiscoverer ?? new Discoverer();
    }

    protected function getServiceAccount(): ServiceAccount
    {
        if (!$this->serviceAccount) {
            $this->serviceAccount = $this->getServiceAccountDiscoverer()->discover();
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
        return uri_for(sprintf(self::$databaseUriPattern, $serviceAccount->getSanitizedProjectId()));
    }

    protected function getStorageBucketNameFromServiceAccount(ServiceAccount $serviceAccount): string
    {
        return sprintf(self::$storageBucketNamePattern, $serviceAccount->getSanitizedProjectId());
    }

    protected function createAuth(): Auth
    {
        $http = $this->createApiClient([
            'base_uri' => 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/',
        ]);
        $apiClient = new Auth\ApiClient($http);

        $serviceAccount = $this->getServiceAccount();

        $customTokenGenerator = $this->createCustomTokenGenerator();
        $keyStore = new HttpKeyStore(new Client(), $this->verifierCache ?: new InMemoryCache());
        $verifier = new Verifier($serviceAccount->getSanitizedProjectId(), $keyStore);

        return new Auth($apiClient, $customTokenGenerator, $verifier);
    }

    public function createCustomTokenGenerator(): Generator
    {
        $serviceAccount = $this->getServiceAccount();

        if ($serviceAccount->hasPrivateKey()) {
            return new CustomTokenGenerator($serviceAccount->getClientEmail(), $serviceAccount->getPrivateKey());
        }

        $http = $this->createApiClient(null, ['https://www.googleapis.com/auth/iam']);

        return new CustomTokenViaGoogleIam($serviceAccount->getClientEmail(), $http);
    }

    protected function createDatabase(): Database
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

    protected function createRemoteConfig(): RemoteConfig
    {
        $http = $this->createApiClient([
            'base_uri' => 'https://firebaseremoteconfig.googleapis.com/v1/projects/'.$this->getServiceAccount()->getSanitizedProjectId().'/remoteConfig',
        ]);

        return new RemoteConfig(new RemoteConfig\ApiClient($http));
    }

    protected function createMessaging(): Messaging
    {
        $serviceAccount = $this->getServiceAccount();
        $projectId = $serviceAccount->getSanitizedProjectId();

        $messagingApiClient = new Messaging\ApiClient(
            $this->createApiClient([
                'base_uri' => 'https://fcm.googleapis.com/v1/projects/'.$projectId,
            ])
        );

        $topicManagementApiClient = new Messaging\TopicManagementApiClient(
            $this->createApiClient([
                'base_uri' => 'https://iid.googleapis.com',
                'headers' => [
                    'access_token_auth' => 'true',
                ],
            ])
        );

        return new Messaging($messagingApiClient, $topicManagementApiClient);
    }

    public function createApiClient(array $config = null, array $additionalScopes = null): Client
    {
        $config = $config ?? [];
        $additionalScopes = $additionalScopes ?? [];

        $googleAuthTokenMiddleware = $this->createGoogleAuthTokenMiddleware($additionalScopes);

        $stack = HandlerStack::create();
        foreach ($this->httpClientMiddlewares as $middleware) {
            $stack->push($middleware);
        }
        $stack->push($googleAuthTokenMiddleware);

        $config = array_merge(
            $this->httpClientConfig,
            $config ?? [],
            [
                'handler' => $stack,
                'auth' => 'google_auth',
            ]
        );

        return new Client($config);
    }

    protected function createGoogleAuthTokenMiddleware(array $additionalScopes = null): AuthTokenMiddleware
    {
        $serviceAccount = $this->getServiceAccount();

        $scopes = [
            'https://www.googleapis.com/auth/cloud-platform',
            'https://www.googleapis.com/auth/firebase',
            'https://www.googleapis.com/auth/firebase.database',
            'https://www.googleapis.com/auth/firebase.messaging',
            'https://www.googleapis.com/auth/firebase.remoteconfig',
            'https://www.googleapis.com/auth/userinfo.email',
        ] + ($additionalScopes ?? []);

        if ((new GcpMetadata())->isAvailable()) {
            $credentials = new GCECredentials();
        } else {
            $credentials = new ServiceAccountCredentials($scopes, [
                'client_email' => $serviceAccount->getClientEmail(),
                'client_id' => $serviceAccount->getClientId(),
                'private_key' => $serviceAccount->getPrivateKey(),
            ]);
        }

        return new AuthTokenMiddleware($credentials);
    }

    protected function createStorage(): Storage
    {
        $builder = $this->getGoogleCloudServiceBuilder();

        $storageClient = $builder->storage([
            'projectId' => $this->getServiceAccount()->getSanitizedProjectId(),
        ]);

        return new Storage($storageClient, $this->getStorageBucketName());
    }

    protected function getGoogleCloudServiceBuilder(): ServiceBuilder
    {
        $serviceAccount = $this->getServiceAccount();

        $config = [
            'projectId' => $serviceAccount->getProjectId(),
        ];

        if ($serviceAccount->hasClientId() && $serviceAccount->hasPrivateKey()) {
            $config = [
                'keyFile' => [
                    'client_email' => $serviceAccount->getClientEmail(),
                    'client_id' => $serviceAccount->getClientId(),
                    'private_key' => $serviceAccount->getPrivateKey(),
                    'type' => 'service_account',
                ],
            ];
        }

        return new ServiceBuilder($config);
    }
}
