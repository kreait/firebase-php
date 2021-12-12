<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Firebase\Auth\Token\Cache\InMemoryCache;
use Firebase\Auth\Token\Domain\Generator;
use Firebase\Auth\Token\Domain\Verifier;
use Firebase\Auth\Token\Generator as CustomTokenGenerator;
use Firebase\Auth\Token\HttpKeyStore;
use Firebase\Auth\Token\TenantAwareGenerator;
use Firebase\Auth\Token\TenantAwareVerifier;
use Firebase\Auth\Token\Verifier as LegacyIdTokenVerifier;
use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\Cache\MemoryCacheItemPool;
use Google\Auth\Credentials\AppIdentityCredentials;
use Google\Auth\Credentials\GCECredentials;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Auth\CredentialsLoader;
use Google\Auth\FetchAuthTokenCache;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Google\Auth\Middleware\AuthTokenMiddleware;
use Google\Auth\ProjectIdProviderInterface;
use Google\Auth\SignBlobInterface;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Storage\StorageClient;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Psr7\Utils as GuzzleUtils;
use GuzzleHttp\RequestOptions;
use Kreait\Clock;
use Kreait\Clock\SystemClock;
use Kreait\Firebase;
use Kreait\Firebase\Auth\CustomTokenViaGoogleIam;
use Kreait\Firebase\Auth\DisabledLegacyCustomTokenGenerator;
use Kreait\Firebase\Auth\IdTokenVerifier;
use Kreait\Firebase\Auth\TenantId;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Exception\RuntimeException;
use Kreait\Firebase\Http\HttpClientOptions;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\Value\Email;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\SimpleCache\CacheInterface;
use Throwable;

class Factory
{
    public const API_CLIENT_SCOPES = [
        'https://www.googleapis.com/auth/iam',
        'https://www.googleapis.com/auth/cloud-platform',
        'https://www.googleapis.com/auth/firebase',
        'https://www.googleapis.com/auth/firebase.database',
        'https://www.googleapis.com/auth/firebase.messaging',
        'https://www.googleapis.com/auth/firebase.remoteconfig',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/securetoken',
    ];

    protected ?UriInterface $databaseUri = null;

    protected ?string $defaultStorageBucket = null;

    protected ?ServiceAccount $serviceAccount = null;

    protected ?CredentialsLoader $googleAuthTokenCredentials = null;

    protected ?string $projectId = null;

    protected ?string $clientEmail = null;

    protected CacheInterface $verifierCache;

    protected CacheItemPoolInterface $authTokenCache;

    protected bool $discoveryIsDisabled = false;

    protected static string $databaseUriPattern = 'https://%s.firebaseio.com';

    protected static string $storageBucketNamePattern = '%s.appspot.com';

    protected Clock $clock;

    /** @var callable|null */
    protected $httpLogMiddleware;

    /** @var callable|null */
    protected $httpDebugLogMiddleware;

    /** @var callable|null */
    protected $databaseAuthVariableOverrideMiddleware;

    protected ?TenantId $tenantId = null;

    protected HttpClientOptions $httpClientOptions;

    public function __construct()
    {
        $this->clock = new SystemClock();
        $this->verifierCache = new InMemoryCache();
        $this->authTokenCache = new MemoryCacheItemPool();
        $this->httpClientOptions = HttpClientOptions::default();
    }

    /**
     * @param string|array<string, string>|ServiceAccount $value
     */
    public function withServiceAccount($value): self
    {
        $serviceAccount = ServiceAccount::fromValue($value);

        $factory = clone $this;
        $factory->serviceAccount = $serviceAccount;

        return $factory
            ->withProjectId($serviceAccount->getProjectId())
            ->withClientEmail($serviceAccount->getClientEmail())
        ;
    }

    public function withProjectId(string $projectId): self
    {
        $factory = clone $this;
        $factory->projectId = $projectId;

        return $factory;
    }

    public function withClientEmail(string $clientEmail): self
    {
        $factory = clone $this;
        $factory->clientEmail = (string) (new Email($clientEmail));

        return $factory;
    }

    public function withTenantId(string $tenantId): self
    {
        $factory = clone $this;
        $factory->tenantId = TenantId::fromString($tenantId);

        return $factory;
    }

    public function withDisabledAutoDiscovery(): self
    {
        $factory = clone $this;
        $factory->discoveryIsDisabled = true;

        return $factory;
    }

    /**
     * @param UriInterface|string $uri
     */
    public function withDatabaseUri($uri): self
    {
        $factory = clone $this;
        $factory->databaseUri = GuzzleUtils::uriFor($uri);

        return $factory;
    }

    /**
     * The object to use as the `auth` variable in your Realtime Database Rules
     * when the Admin SDK reads from or writes to the Realtime Database.
     *
     * This allows you to downscope the Admin SDK from its default full read and
     * write privileges. You can pass `null` to act as an unauthenticated client.
     *
     * @see https://firebase.google.com/docs/database/admin/start#authenticate-with-limited-privileges
     *
     * @param array<string, mixed>|null $override
     */
    public function withDatabaseAuthVariableOverride(?array $override): self
    {
        $factory = clone $this;
        $factory->databaseAuthVariableOverrideMiddleware = Middleware::addDatabaseAuthVariableOverride($override);

        return $factory;
    }

    public function withDefaultStorageBucket(string $name): self
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

    public function withAuthTokenCache(CacheItemPoolInterface $cache): self
    {
        $factory = clone $this;
        $factory->authTokenCache = $cache;

        return $factory;
    }

    public function withHttpClientOptions(HttpClientOptions $options): self
    {
        $factory = clone $this;
        $factory->httpClientOptions = $options;

        return $factory;
    }

    public function withHttpLogger(LoggerInterface $logger, ?MessageFormatter $formatter = null, ?string $logLevel = null, ?string $errorLogLevel = null): self
    {
        $formatter = $formatter ?: new MessageFormatter();
        $logLevel = $logLevel ?: LogLevel::INFO;
        $errorLogLevel = $errorLogLevel ?: LogLevel::NOTICE;

        $factory = clone $this;
        $factory->httpLogMiddleware = Middleware::log($logger, $formatter, $logLevel, $errorLogLevel);

        return $factory;
    }

    public function withHttpDebugLogger(LoggerInterface $logger, ?MessageFormatter $formatter = null, ?string $logLevel = null, ?string $errorLogLevel = null): self
    {
        $formatter = $formatter ?: new MessageFormatter(MessageFormatter::DEBUG);
        $logLevel = $logLevel ?: LogLevel::INFO;
        $errorLogLevel = $errorLogLevel ?: LogLevel::NOTICE;

        $factory = clone $this;
        $factory->httpDebugLogMiddleware = Middleware::log($logger, $formatter, $logLevel, $errorLogLevel);

        return $factory;
    }

    public function withClock(Clock $clock): self
    {
        $factory = clone $this;
        $factory->clock = $clock;

        return $factory;
    }

    protected function getServiceAccount(): ?ServiceAccount
    {
        if ($this->serviceAccount !== null) {
            return $this->serviceAccount;
        }

        if ($credentials = Util::getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            try {
                return $this->serviceAccount = ServiceAccount::fromValue($credentials);
            } catch (InvalidArgumentException $e) {
                // Do nothing, continue trying
            }
        }

        if ($this->discoveryIsDisabled) {
            return null;
        }

        // @codeCoverageIgnoreStart
        // We can't reliably test this without re-implementing it ourselves
        if ($credentials = CredentialsLoader::fromWellKnownFile()) {
            try {
                return $this->serviceAccount = ServiceAccount::fromValue($credentials);
            } catch (InvalidArgumentException $e) {
                // Do nothing, continue trying
            }
        }
        // @codeCoverageIgnoreEnd

        // ... or don't
        return null;
    }

    protected function getProjectId(): string
    {
        if ($this->projectId !== null) {
            return $this->projectId;
        }

        $serviceAccount = $this->getServiceAccount();

        if ($serviceAccount !== null) {
            return $this->projectId = $serviceAccount->getProjectId();
        }

        if ($this->discoveryIsDisabled) {
            throw new RuntimeException('Unable to determine the Firebase Project ID, and credential discovery is disabled');
        }

        if (
            ($credentials = $this->getGoogleAuthTokenCredentials())
            && ($credentials instanceof ProjectIdProviderInterface)
            && ($projectId = $credentials->getProjectId())
        ) {
            return $this->projectId = $projectId;
        }

        if ($projectId = Util::getenv('GOOGLE_CLOUD_PROJECT')) {
            return $this->projectId = $projectId;
        }

        throw new RuntimeException('Unable to determine the Firebase Project ID');
    }

    protected function getClientEmail(): ?string
    {
        if ($this->clientEmail !== null) {
            return $this->clientEmail;
        }

        $serviceAccount = $this->getServiceAccount();

        if ($serviceAccount !== null) {
            return $this->clientEmail = (string) (new Email($serviceAccount->getClientEmail()));
        }

        if ($this->discoveryIsDisabled) {
            return null;
        }

        try {
            if (
                ($credentials = $this->getGoogleAuthTokenCredentials())
                && ($credentials instanceof SignBlobInterface)
                && ($clientEmail = $credentials->getClientName())
            ) {
                return $this->clientEmail = $clientEmail;
            }
        } catch (Throwable $e) {
            return null;
        }

        return null;
    }

    protected function getDatabaseUri(): UriInterface
    {
        if ($this->databaseUri === null) {
            $this->databaseUri = GuzzleUtils::uriFor(\sprintf(self::$databaseUriPattern, $this->getProjectId()));
        }

        return $this->databaseUri;
    }

    protected function getStorageBucketName(): string
    {
        if ($this->defaultStorageBucket === null) {
            $this->defaultStorageBucket = \sprintf(self::$storageBucketNamePattern, $this->getProjectId());
        }

        return $this->defaultStorageBucket;
    }

    public function createAuth(): Contract\Auth
    {
        $projectId = $this->getProjectId();
        $tenantId = $this->tenantId;

        $httpClient = $this->createApiClient([
            'base_uri' => 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/',
        ]);

        $authApiClient = new Auth\ApiClient($httpClient, $tenantId);
        $customTokenGenerator = $this->createCustomTokenGenerator();
        $idTokenVerifier = $this->createIdTokenVerifier();
        $signInHandler = new Firebase\Auth\SignIn\GuzzleHandler($httpClient);

        return new Auth($authApiClient, $httpClient, $customTokenGenerator, $idTokenVerifier, $signInHandler, $projectId, $tenantId);
    }

    public function createCustomTokenGenerator(): Generator
    {
        $serviceAccount = $this->getServiceAccount();
        $clientEmail = $this->getClientEmail();
        $privateKey = $serviceAccount !== null ? $serviceAccount->getPrivateKey() : '';

        if ($clientEmail && $privateKey !== '') {
            if ($this->tenantId !== null) {
                return new TenantAwareGenerator($this->tenantId->toString(), (string) $clientEmail, $privateKey);
            }

            return new CustomTokenGenerator((string) $clientEmail, $privateKey);
        }

        if ($clientEmail !== null) {
            return new CustomTokenViaGoogleIam((string) $clientEmail, $this->createApiClient(), $this->tenantId);
        }

        return new DisabledLegacyCustomTokenGenerator(
            'Custom Token Generation is disabled because the current credentials do not permit it'
        );
    }

    public function createIdTokenVerifier(): Verifier
    {
        $keyStore = new HttpKeyStore(new Client(), $this->verifierCache);

        $baseVerifier = new LegacyIdTokenVerifier($this->getProjectId(), $keyStore);

        if ($this->tenantId !== null) {
            $baseVerifier = new TenantAwareVerifier($this->tenantId->toString(), $baseVerifier);
        }

        return new IdTokenVerifier($baseVerifier, $this->clock);
    }

    public function createDatabase(): Contract\Database
    {
        $http = $this->createApiClient();

        /** @var HandlerStack $handler */
        $handler = $http->getConfig('handler');
        $handler->push(Firebase\Http\Middleware::ensureJsonSuffix(), 'realtime_database_json_suffix');

        if ($this->databaseAuthVariableOverrideMiddleware) {
            $handler->push($this->databaseAuthVariableOverrideMiddleware, 'database_auth_variable_override');
        }

        return new Database($this->getDatabaseUri(), new Database\ApiClient($http));
    }

    public function createRemoteConfig(): Contract\RemoteConfig
    {
        $http = $this->createApiClient([
            'base_uri' => "https://firebaseremoteconfig.googleapis.com/v1/projects/{$this->getProjectId()}/remoteConfig",
        ]);

        return new RemoteConfig(new RemoteConfig\ApiClient($http));
    }

    public function createMessaging(): Contract\Messaging
    {
        $projectId = $this->getProjectId();

        $errorHandler = new MessagingApiExceptionConverter($this->clock);

        $messagingApiClient = new Messaging\ApiClient(
            $this->createApiClient([
                'base_uri' => 'https://fcm.googleapis.com/v1/projects/'.$projectId,
            ]),
            $errorHandler
        );

        $appInstanceApiClient = new Messaging\AppInstanceApiClient(
            $this->createApiClient([
                'base_uri' => 'https://iid.googleapis.com',
                'headers' => [
                    'access_token_auth' => 'true',
                ],
            ]),
            $errorHandler
        );

        return new Messaging($projectId, $messagingApiClient, $appInstanceApiClient);
    }

    /**
     * @param \Stringable|string|null $defaultDynamicLinksDomain
     */
    public function createDynamicLinksService($defaultDynamicLinksDomain = null): Contract\DynamicLinks
    {
        $apiClient = $this->createApiClient();

        if ($defaultDynamicLinksDomain !== null) {
            return DynamicLinks::withApiClientAndDefaultDomain($apiClient, $defaultDynamicLinksDomain);
        }

        return DynamicLinks::withApiClient($apiClient);
    }

    public function createFirestore(): Contract\Firestore
    {
        $config = [];
        $projectId = $this->getProjectId();
        $serviceAccount = $this->getServiceAccount();

        if ($serviceAccount !== null) {
            $config['keyFile'] = $serviceAccount->asArray();
        } elseif ($this->discoveryIsDisabled) {
            throw new RuntimeException('Unable to create a Firestore Client without credentials');
        }

        if ($projectId !== null) {
            $config['projectId'] = $projectId;
        }

        try {
            $firestoreClient = new FirestoreClient($config);
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to create a FirestoreClient: '.$e->getMessage(), $e->getCode(), $e);
        }

        return Firestore::withFirestoreClient($firestoreClient);
    }

    public function createStorage(): Contract\Storage
    {
        $config = [
            'projectId' => $this->getProjectId(),
        ];

        $serviceAccount = $this->getServiceAccount();

        if ($serviceAccount !== null) {
            $config['keyFile'] = $serviceAccount->asArray();
        } elseif ($this->discoveryIsDisabled) {
            throw new RuntimeException('Unable to create a Storage Client without credentials');
        }

        try {
            $storageClient = new StorageClient($config);
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to create a Storage Client: '.$e->getMessage(), $e->getCode(), $e);
        }

        return new Storage($storageClient, $this->getStorageBucketName());
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array{
     *     credentialsType: string|null,
     *     databaseUrl: string,
     *     defaultStorageBucket: string|null,
     *     serviceAccount: null|string|array<string, string>,
     *     projectId: string,
     *     tenantId: string|null,
     *     tokenCacheType: class-string,
     *     verifierCacheType: class-string,
     * }
     */
    public function getDebugInfo(): array
    {
        try {
            $projectId = $this->getProjectId();
        } catch (Throwable $e) {
            $projectId = $e->getMessage();
        }

        try {
            $credentials = $this->getGoogleAuthTokenCredentials();

            if ($credentials !== null) {
                $credentials = \get_class($credentials);
            }
        } catch (Throwable $e) {
            $credentials = $e->getMessage();
        }

        try {
            if (($serviceAccount = $this->getServiceAccount()) !== null) {
                $serviceAccount = $serviceAccount->asArray();
                if (\array_key_exists('private_key', $serviceAccount)) {
                    $serviceAccount['private_key'] = '{exists, redacted}';
                }
            }
        } catch (Throwable $e) {
            $serviceAccount = $e->getMessage();
        }

        try {
            $databaseUrl = (string) $this->getDatabaseUri();
        } catch (Throwable $e) {
            $databaseUrl = $e->getMessage();
        }

        return [
            'credentialsType' => $credentials,
            'databaseUrl' => $databaseUrl,
            'defaultStorageBucket' => $this->defaultStorageBucket,
            'projectId' => $projectId,
            'serviceAccount' => $serviceAccount,
            'tenantId' => $this->tenantId ? $this->tenantId->toString() : null,
            'tokenCacheType' => \get_class($this->authTokenCache),
            'verifierCacheType' => \get_class($this->verifierCache),
        ];
    }

    /**
     * @internal
     *
     * @param array<string, mixed>|null $config
     */
    public function createApiClient(?array $config = null): Client
    {
        $config ??= [];

        if ($proxy = $this->httpClientOptions->proxy()) {
            $config[RequestOptions::PROXY] = $proxy;
        }

        if ($connectTimeout = $this->httpClientOptions->connectTimeout()) {
            $config[RequestOptions::CONNECT_TIMEOUT] = $connectTimeout;
        }

        if ($readTimeout = $this->httpClientOptions->readTimeout()) {
            $config[RequestOptions::READ_TIMEOUT] = $readTimeout;
        }

        if ($totalTimeout = $this->httpClientOptions->timeout()) {
            $config[RequestOptions::TIMEOUT] = $totalTimeout;
        }

        $handler = $config['handler'] ?? null;

        if (!($handler instanceof HandlerStack)) {
            $handler = HandlerStack::create($handler);
        }

        if ($this->httpLogMiddleware) {
            $handler->push($this->httpLogMiddleware, 'http_logs');
        }

        if ($this->httpDebugLogMiddleware) {
            $handler->push($this->httpDebugLogMiddleware, 'http_debug_logs');
        }

        $credentials = $this->getGoogleAuthTokenCredentials();

        if ($credentials !== null) {
            $projectId = $credentials instanceof ProjectIdProviderInterface ? $credentials->getProjectId() : 'project';
            $cachePrefix = 'kreait_firebase_'.$projectId;

            $credentials = new FetchAuthTokenCache($credentials, ['prefix' => $cachePrefix], $this->authTokenCache);
            $authTokenHandlerConfig = $config;
            $authTokenHandlerConfig['handler'] = clone $handler;

            $authTokenHandler = HttpHandlerFactory::build(new Client($authTokenHandlerConfig));

            $handler->push(new AuthTokenMiddleware($credentials, $authTokenHandler));
        }

        $handler->push(Middleware::responseWithSubResponses());

        $config['handler'] = $handler;
        $config['auth'] = 'google_auth';

        return new Client($config);
    }

    /**
     * @internal
     *
     * @param ServiceAccountCredentials|UserRefreshCredentials|AppIdentityCredentials|GCECredentials|CredentialsLoader $credentials
     */
    public function withGoogleAuthTokenCredentials($credentials): self
    {
        $factory = clone $this;
        $factory->googleAuthTokenCredentials = $credentials;

        return $factory;
    }

    protected function getGoogleAuthTokenCredentials(): ?CredentialsLoader
    {
        if ($this->googleAuthTokenCredentials !== null) {
            return $this->googleAuthTokenCredentials;
        }

        $serviceAccount = $this->getServiceAccount();

        if ($serviceAccount !== null) {
            return $this->googleAuthTokenCredentials = new ServiceAccountCredentials(self::API_CLIENT_SCOPES, $serviceAccount->asArray());
        }

        if ($this->discoveryIsDisabled) {
            return null;
        }

        try {
            return $this->googleAuthTokenCredentials = ApplicationDefaultCredentials::getCredentials(self::API_CLIENT_SCOPES);
        } catch (Throwable $e) {
            return null;
        }
    }
}
