<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Beste\Cache\InMemoryCache;
use Beste\Clock\SystemClock;
use Beste\Clock\WrappingClock;
use Beste\Json;
use Firebase\JWT\CachedKeySet;
use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\FetchAuthTokenCache;
use Google\Auth\FetchAuthTokenInterface;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Google\Auth\Middleware\AuthTokenMiddleware;
use Google\Auth\ProjectIdProviderInterface;
use Google\Auth\SignBlobInterface;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Storage\StorageClient;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Utils as GuzzleUtils;
use Kreait\Firebase\AppCheck\AppCheckTokenGenerator;
use Kreait\Firebase\AppCheck\AppCheckTokenVerifier;
use Kreait\Firebase\Auth\ApiClient;
use Kreait\Firebase\Auth\CustomTokenViaGoogleCredentials;
use Kreait\Firebase\Auth\SignIn\GuzzleHandler;
use Kreait\Firebase\Database\UrlBuilder;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Exception\RuntimeException;
use Kreait\Firebase\Http\HttpClientOptions;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\JWT\IdTokenVerifier;
use Kreait\Firebase\JWT\SessionCookieVerifier;
use Kreait\Firebase\Messaging\AppInstanceApiClient;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;
use Throwable;
use UnexpectedValueException;

use function array_filter;
use function is_array;
use function is_string;
use function sprintf;
use function trim;

/**
 * @phpstan-type ServiceAccountShape array{
 *     project_id: non-empty-string,
 *     client_email: non-empty-string,
 *     private_key: non-empty-string,
 *     type: 'service_account'
 * }
 */
final class Factory
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

    /**
     * @var non-empty-string|null
     */
    private ?string $databaseUrl = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $defaultStorageBucket = null;

    /**
     * @var ServiceAccountShape|null
     */
    private ?array $serviceAccount = null;
    private ?FetchAuthTokenInterface $googleAuthTokenCredentials = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $projectId = null;
    private CacheItemPoolInterface $verifierCache;
    private CacheItemPoolInterface $authTokenCache;
    private CacheItemPoolInterface $keySetCache;
    private ClockInterface $clock;

    /**
     * @var callable|null
     */
    private $httpLogMiddleware;

    /**
     * @var callable|null
     */
    private $httpDebugLogMiddleware;

    /**
     * @var callable|null
     */
    private $databaseAuthVariableOverrideMiddleware;

    /**
     * @var non-empty-string|null
     */
    private ?string $tenantId = null;
    private HttpFactory $httpFactory;
    private HttpClientOptions $httpClientOptions;

    /**
     * @var array<non-empty-string, mixed>
     */
    private array $firestoreClientConfig = [];

    public function __construct()
    {
        $this->clock = SystemClock::create();
        $this->httpFactory = new HttpFactory();
        $this->verifierCache = new InMemoryCache($this->clock);
        $this->authTokenCache = new InMemoryCache($this->clock);
        $this->keySetCache = new InMemoryCache($this->clock);
        $this->httpClientOptions = HttpClientOptions::default();

        $googleApplicationCredentials = Util::getenv('GOOGLE_APPLICATION_CREDENTIALS');

        if ($googleApplicationCredentials === null) {
            return;
        }

        if (!str_starts_with($googleApplicationCredentials, '{')) {
            return;
        }

        $this->serviceAccount = Json::decode($googleApplicationCredentials, true);
    }

    /**
     * @param non-empty-string|ServiceAccountShape $value
     */
    public function withServiceAccount(string|array $value): self
    {
        $serviceAccount = $value;

        if (is_string($value) && str_starts_with($value, '{')) {
            try {
                $serviceAccount = Json::decode($value, true);
            } catch (UnexpectedValueException $e) {
                throw new InvalidArgumentException('Invalid service account: '.$e->getMessage(), $e->getCode(), $e);
            }
        } elseif (is_string($value)) {
            try {
                $serviceAccount = Json::decodeFile($value, true);
            } catch (UnexpectedValueException $e) {
                throw new InvalidArgumentException('Invalid service account: '.$e->getMessage(), $e->getCode(), $e);
            }
        }

        $factory = clone $this;
        $factory->serviceAccount = $serviceAccount;

        return $factory;
    }

    /**
     * @param non-empty-string $projectId
     */
    public function withProjectId(string $projectId): self
    {
        $factory = clone $this;
        $factory->projectId = $projectId;

        return $factory;
    }

    /**
     * @param non-empty-string $tenantId
     */
    public function withTenantId(string $tenantId): self
    {
        $factory = clone $this;
        $factory->tenantId = $tenantId;

        return $factory;
    }

    /**
     * @param UriInterface|non-empty-string $uri
     */
    public function withDatabaseUri(UriInterface|string $uri): self
    {
        $url = trim($uri instanceof UriInterface ? $uri->__toString() : $uri);

        if ($url === '') {
            throw new InvalidArgumentException('The database URI cannot be empty');
        }

        $factory = clone $this;
        $factory->databaseUrl = $url;

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
     * @param array<non-empty-string, mixed>|null $override
     */
    public function withDatabaseAuthVariableOverride(?array $override): self
    {
        $factory = clone $this;
        $factory->databaseAuthVariableOverrideMiddleware = Middleware::addDatabaseAuthVariableOverride($override);

        return $factory;
    }

    /**
     * @param non-empty-string $database
     */
    public function withFirestoreDatabase(string $database): self
    {
        return $this->withFirestoreClientConfig(['database' => $database]);
    }

    /**
     * @param array<non-empty-string, mixed> $config
     */
    public function withFirestoreClientConfig(array $config): self
    {
        $factory = clone $this;
        $factory->firestoreClientConfig = array_merge($this->firestoreClientConfig, $config);

        return $factory;
    }

    /**
     * @param non-empty-string $name
     */
    public function withDefaultStorageBucket(string $name): self
    {
        $factory = clone $this;
        $factory->defaultStorageBucket = $name;

        return $factory;
    }

    public function withVerifierCache(CacheItemPoolInterface $cache): self
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

    public function withKeySetCache(CacheItemPoolInterface $cache): self
    {
        $factory = clone $this;
        $factory->keySetCache = $cache;

        return $factory;
    }

    public function withHttpClientOptions(HttpClientOptions $options): self
    {
        $factory = clone $this;
        $factory->httpClientOptions = $options;

        return $factory;
    }

    /**
     * @param non-empty-string|null $logLevel
     * @param non-empty-string|null $errorLogLevel
     */
    public function withHttpLogger(LoggerInterface $logger, ?MessageFormatter $formatter = null, ?string $logLevel = null, ?string $errorLogLevel = null): self
    {
        $formatter = $formatter ?: new MessageFormatter();
        $logLevel = $logLevel ?: LogLevel::INFO;
        $errorLogLevel = $errorLogLevel ?: LogLevel::NOTICE;

        $factory = clone $this;
        $factory->httpLogMiddleware = Middleware::log($logger, $formatter, $logLevel, $errorLogLevel);

        return $factory;
    }

    /**
     * @param non-empty-string|null $logLevel
     * @param non-empty-string|null $errorLogLevel
     */
    public function withHttpDebugLogger(LoggerInterface $logger, ?MessageFormatter $formatter = null, ?string $logLevel = null, ?string $errorLogLevel = null): self
    {
        $formatter = $formatter ?: new MessageFormatter(MessageFormatter::DEBUG);
        $logLevel = $logLevel ?: LogLevel::INFO;
        $errorLogLevel = $errorLogLevel ?: LogLevel::NOTICE;

        $factory = clone $this;
        $factory->httpDebugLogMiddleware = Middleware::log($logger, $formatter, $logLevel, $errorLogLevel);

        return $factory;
    }

    public function withClock(object $clock): self
    {
        if (!$clock instanceof ClockInterface) {
            $clock = WrappingClock::wrapping($clock);
        }

        $factory = clone $this;
        $factory->clock = $clock;

        return $factory;
    }

    public function createAppCheck(): Contract\AppCheck
    {
        $projectId = $this->getProjectId();

        if ($this->serviceAccount === null) {
            throw new RuntimeException('Unable to use AppCheck without credentials');
        }

        $http = $this->createApiClient([
            'base_uri' => 'https://firebaseappcheck.googleapis.com/v1/projects/'.$projectId.'/',
        ]);

        $keySet = new CachedKeySet(
            'https://firebaseappcheck.googleapis.com/v1/jwks',
            new Client($this->httpClientOptions->guzzleConfig()),
            $this->httpFactory,
            $this->keySetCache,
            21600,
            true,
        );

        return new AppCheck(
            new AppCheck\ApiClient($http),
            new AppCheckTokenGenerator(
                $this->serviceAccount['client_email'],
                $this->serviceAccount['private_key'],
                $this->clock,
            ),
            new AppCheckTokenVerifier($projectId, $keySet),
        );
    }

    public function createAuth(): Contract\Auth
    {
        $projectId = $this->getProjectId();

        $httpClient = $this->createApiClient();

        $signInHandler = new GuzzleHandler($projectId, $httpClient);
        $authApiClient = new ApiClient($projectId, $this->tenantId, $httpClient, $signInHandler, $this->clock);
        $customTokenGenerator = $this->createCustomTokenGenerator();
        $idTokenVerifier = $this->createIdTokenVerifier();
        $sessionCookieVerifier = $this->createSessionCookieVerifier();

        return new Auth($authApiClient, $customTokenGenerator, $idTokenVerifier, $sessionCookieVerifier, $this->clock);
    }

    public function createDatabase(): Contract\Database
    {
        $middlewares = array_filter([
            Middleware::ensureJsonSuffix(),
            $this->databaseAuthVariableOverrideMiddleware,
        ]);

        $http = $this->createApiClient(null, $middlewares);
        $databaseUrl = $this->getDatabaseUrl();
        $resourceUrlBuilder = UrlBuilder::create($databaseUrl);

        return new Database(
            GuzzleUtils::uriFor($databaseUrl),
            new Database\ApiClient($http, $resourceUrlBuilder),
            $resourceUrlBuilder,
        );
    }

    public function createRemoteConfig(): Contract\RemoteConfig
    {
        $http = $this->createApiClient([
            'base_uri' => "https://firebaseremoteconfig.googleapis.com/v1/projects/{$this->getProjectId()}/remoteConfig",
        ]);

        return new RemoteConfig(new RemoteConfig\ApiClient($this->getProjectId(), $http));
    }

    public function createMessaging(): Contract\Messaging
    {
        $projectId = $this->getProjectId();

        $errorHandler = new MessagingApiExceptionConverter($this->clock);

        $messagingApiClient = new Messaging\ApiClient(
            $this->createApiClient(),
            $projectId,
            $this->httpFactory,
            $this->httpFactory,
        );

        $appInstanceApiClient = new AppInstanceApiClient(
            $this->createApiClient([
                'base_uri' => 'https://iid.googleapis.com',
                'headers' => [
                    'access_token_auth' => 'true',
                ],
            ]),
            $errorHandler,
        );

        return new Messaging($messagingApiClient, $appInstanceApiClient, $errorHandler);
    }

    /**
     * @param Stringable|non-empty-string|null $defaultDynamicLinksDomain
     */
    public function createDynamicLinksService($defaultDynamicLinksDomain = null): Contract\DynamicLinks
    {
        $apiClient = new DynamicLink\ApiClient(
            $this->createApiClient(),
            $this->httpFactory,
            $this->httpFactory,
        );

        $defaultDynamicLinksDomain = trim((string) $defaultDynamicLinksDomain);

        if ($defaultDynamicLinksDomain !== '') {
            return DynamicLinks::withApiClientAndDefaultDomain($apiClient, $defaultDynamicLinksDomain);
        }

        return DynamicLinks::withApiClient($apiClient);
    }

    public function createFirestore(): Contract\Firestore
    {
        $config = $this->googleCloudClientConfig() + $this->firestoreClientConfig;

        try {
            $firestoreClient = new FirestoreClient($config);
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to create a FirestoreClient: '.$e->getMessage(), $e->getCode(), $e);
        }

        return Firestore::withFirestoreClient($firestoreClient);
    }

    public function createStorage(): Contract\Storage
    {
        try {
            $storageClient = new StorageClient($this->googleCloudClientConfig());
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to create a StorageClient: '.$e->getMessage(), $e->getCode(), $e);
        }

        return new Storage($storageClient, $this->getStorageBucketName());
    }

    /**
     * @return array{
     *     credentialsType: string|null,
     *     databaseUrl: string,
     *     defaultStorageBucket: string|null,
     *     serviceAccount: string|array<string, string>|null,
     *     projectId: string,
     *     tenantId: non-empty-string|null,
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
                $credentials = $credentials::class;
            }
        } catch (Throwable $e) {
            $credentials = $e->getMessage();
        }

        try {
            $databaseUrl = $this->getDatabaseUrl();
        } catch (Throwable $e) {
            $databaseUrl = $e->getMessage();
        }

        return [
            'credentialsType' => $credentials,
            'databaseUrl' => $databaseUrl,
            'defaultStorageBucket' => $this->defaultStorageBucket,
            'projectId' => $projectId,
            'serviceAccount' => $this->serviceAccount,
            'tenantId' => $this->tenantId,
            'tokenCacheType' => $this->authTokenCache::class,
            'verifierCacheType' => $this->verifierCache::class,
        ];
    }

    /**
     * @param array<non-empty-string, mixed>|null $config
     * @param array<callable(callable): callable>|null $middlewares
     */
    public function createApiClient(?array $config = null, ?array $middlewares = null): Client
    {
        $config ??= [];
        $middlewares ??= [];

        $config = [...$this->httpClientOptions->guzzleConfig(), ...$config];

        $handler = HandlerStack::create();

        if ($this->httpLogMiddleware) {
            $handler->push($this->httpLogMiddleware, 'http_logs');
        }

        if ($this->httpDebugLogMiddleware) {
            $handler->push($this->httpDebugLogMiddleware, 'http_debug_logs');
        }

        foreach ($this->httpClientOptions->guzzleMiddlewares() as $middleware) {
            $handler->push($middleware['middleware'], $middleware['name']);
        }

        foreach ($middlewares as $middleware) {
            $handler->push($middleware);
        }

        $credentials = $this->getGoogleAuthTokenCredentials();

        if (!$credentials instanceof FetchAuthTokenInterface) {
            throw new RuntimeException('Unable to create an API client without credentials');
        }

        $projectId = $this->getProjectId();
        $cachePrefix = 'kreait_firebase_'.$projectId;

        $credentials = new FetchAuthTokenCache($credentials, ['prefix' => $cachePrefix], $this->authTokenCache);
        $authTokenHandler = HttpHandlerFactory::build(new Client($config));

        $handler->push(new AuthTokenMiddleware($credentials, $authTokenHandler));

        $config['handler'] = $handler;
        $config['auth'] = 'google_auth';

        return new Client($config);
    }

    /**
     * @return array{
     *     projectId: non-empty-string,
     *     authCache: CacheItemPoolInterface,
     *     credentialsFetcher?: FetchAuthTokenInterface,
     *     keyFile?: ServiceAccountShape,
     *     keyFilePath?: non-empty-string
     * }
     */
    private function googleCloudClientConfig(): array
    {
        $config = [
            'projectId' => $this->getProjectId(),
            'authCache' => $this->authTokenCache,
        ];

        if ($credentials = $this->getGoogleAuthTokenCredentials()) {
            $config['credentialsFetcher'] = $credentials;
        }

        if (is_array($this->serviceAccount)) {
            $config['keyFile'] = $this->serviceAccount;
        }

        return $config;
    }

    /**
     * @return non-empty-string
     */
    private function getProjectId(): string
    {
        if ($this->projectId !== null) {
            return $this->projectId;
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

    /**
     * @return non-empty-string
     */
    private function getDatabaseUrl(): string
    {
        if ($this->databaseUrl === null) {
            $this->databaseUrl = sprintf('https://%s.firebaseio.com', $this->getProjectId());
        }

        return $this->databaseUrl;
    }

    /**
     * @return non-empty-string
     */
    private function getStorageBucketName(): string
    {
        if ($this->defaultStorageBucket === null) {
            $this->defaultStorageBucket = sprintf('%s.appspot.com', $this->getProjectId());
        }

        return $this->defaultStorageBucket;
    }

    private function createCustomTokenGenerator(): ?CustomTokenViaGoogleCredentials
    {
        $credentials = $this->getGoogleAuthTokenCredentials();

        if ($credentials instanceof SignBlobInterface) {
            return new CustomTokenViaGoogleCredentials($credentials, $this->tenantId);
        }

        return null;
    }

    private function createIdTokenVerifier(): IdTokenVerifier
    {
        $verifier = IdTokenVerifier::createWithProjectIdAndCache($this->getProjectId(), $this->verifierCache);

        if ($this->tenantId === null) {
            return $verifier;
        }

        return $verifier->withExpectedTenantId($this->tenantId);
    }

    private function createSessionCookieVerifier(): SessionCookieVerifier
    {
        return SessionCookieVerifier::createWithProjectIdAndCache($this->getProjectId(), $this->verifierCache);
    }

    private function getGoogleAuthTokenCredentials(): ?FetchAuthTokenInterface
    {
        if ($this->googleAuthTokenCredentials !== null) {
            return $this->googleAuthTokenCredentials;
        }

        if ($this->serviceAccount !== null) {
            return $this->googleAuthTokenCredentials = new ServiceAccountCredentials(self::API_CLIENT_SCOPES, $this->serviceAccount);
        }

        try {
            return $this->googleAuthTokenCredentials = ApplicationDefaultCredentials::getCredentials(self::API_CLIENT_SCOPES);
        } catch (Throwable) {
            return null;
        }
    }
}
