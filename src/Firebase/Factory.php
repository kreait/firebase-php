<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Beste\Clock\SystemClock;
use Beste\Clock\WrappingClock;
use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\Cache\MemoryCacheItemPool;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\CredentialsLoader;
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
use GuzzleHttp\Psr7\Utils as GuzzleUtils;
use GuzzleHttp\RequestOptions;
use Kreait\Firebase;
use Kreait\Firebase\Auth\CustomTokenViaGoogleIam;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Exception\RuntimeException;
use Kreait\Firebase\Http\HttpClientOptions;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\JWT\CustomTokenGenerator;
use Kreait\Firebase\JWT\IdTokenVerifier;
use Kreait\Firebase\JWT\SessionCookieVerifier;
use Kreait\Firebase\Value\Email;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use StellaMaris\Clock\ClockInterface;
use Throwable;

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

    private ?string $databaseUri = null;

    private ?string $defaultStorageBucket = null;

    private ?ServiceAccount $serviceAccount = null;

    private ?FetchAuthTokenInterface $googleAuthTokenCredentials = null;

    private ?string $projectId = null;

    private ?string $clientEmail = null;

    private CacheItemPoolInterface $verifierCache;

    private CacheItemPoolInterface $authTokenCache;

    private bool $discoveryIsDisabled = false;

    private static string $databaseUriPattern = 'https://%s.firebaseio.com';

    private static string $storageBucketNamePattern = '%s.appspot.com';

    private ClockInterface $clock;

    /** @var callable|null */
    private $httpLogMiddleware;

    /** @var callable|null */
    private $httpDebugLogMiddleware;

    /** @var callable|null */
    private $databaseAuthVariableOverrideMiddleware;

    private ?string $tenantId = null;

    private HttpClientOptions $httpClientOptions;

    public function __construct()
    {
        $this->clock = SystemClock::create();
        $this->verifierCache = new MemoryCacheItemPool();
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

        return $factory;
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
        $factory->tenantId = $tenantId;

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
        $factory->databaseUri = (string) GuzzleUtils::uriFor($uri);

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

    public function withClock(object $clock): self
    {
        if (!$clock instanceof ClockInterface) {
            $clock = WrappingClock::wrapping($clock);
        }

        $factory = clone $this;
        $factory->clock = $clock;

        return $factory;
    }

    private function getServiceAccount(): ?ServiceAccount
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

    private function getProjectId(): string
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

    private function getClientEmail(): ?string
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

    private function getDatabaseUri(): UriInterface
    {
        if ($this->databaseUri === null) {
            $this->databaseUri = \sprintf(self::$databaseUriPattern, $this->getProjectId());
        }

        return GuzzleUtils::uriFor($this->databaseUri);
    }

    private function getStorageBucketName(): string
    {
        if ($this->defaultStorageBucket === null) {
            $this->defaultStorageBucket = \sprintf(self::$storageBucketNamePattern, $this->getProjectId());
        }

        return $this->defaultStorageBucket;
    }

    public function createAuth(): Contract\Auth
    {
        $projectId = $this->getProjectId();

        $httpClient = $this->createApiClient();

        $signInHandler = new Firebase\Auth\SignIn\GuzzleHandler($projectId, $httpClient);
        $authApiClient = new Auth\ApiClient($projectId, $this->tenantId, $httpClient, $signInHandler, $this->clock);
        $customTokenGenerator = $this->createCustomTokenGenerator();
        $idTokenVerifier = $this->createIdTokenVerifier();
        $sessionCookieVerifier = $this->createSessionCookieVerifier();

        return new Auth($authApiClient, $customTokenGenerator, $idTokenVerifier, $sessionCookieVerifier, $this->clock);
    }

    /**
     * @return CustomTokenGenerator|CustomTokenViaGoogleIam|null
     */
    private function createCustomTokenGenerator()
    {
        $serviceAccount = $this->getServiceAccount();
        $clientEmail = $this->getClientEmail();
        $privateKey = $serviceAccount !== null ? $serviceAccount->getPrivateKey() : null;

        if ($clientEmail && $privateKey) {
            $generator = CustomTokenGenerator::withClientEmailAndPrivateKey($clientEmail, $privateKey);

            if ($this->tenantId !== null) {
                $generator = $generator->withTenantId($this->tenantId);
            }

            return $generator;
        }

        if ($clientEmail !== null) {
            return new CustomTokenViaGoogleIam($clientEmail, $this->createApiClient(), $this->tenantId);
        }

        return null;
    }

    private function createIdTokenVerifier(): IdTokenVerifier
    {
        $verifier = IdTokenVerifier::createWithProjectIdAndCache($this->getProjectId(), $this->verifierCache);

        if ($this->tenantId !== null) {
            $verifier = $verifier->withExpectedTenantId($this->tenantId);
        }

        return $verifier;
    }

    private function createSessionCookieVerifier(): SessionCookieVerifier
    {
        return SessionCookieVerifier::createWithProjectIdAndCache($this->getProjectId(), $this->verifierCache);
    }

    public function createDatabase(): Contract\Database
    {
        $middlewares = \array_filter([
            Firebase\Http\Middleware::ensureJsonSuffix(),
            $this->databaseAuthVariableOverrideMiddleware,
        ]);

        $http = $this->createApiClient(null, $middlewares);

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
        $config = [
            'projectId' => $this->getProjectId(),
        ];

        $serviceAccount = $this->getServiceAccount();

        if ($serviceAccount !== null) {
            $config['keyFile'] = $serviceAccount->asArray();
        } elseif ($this->discoveryIsDisabled) {
            throw new RuntimeException('Unable to create a Firestore Client without credentials');
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
            'tenantId' => $this->tenantId,
            'tokenCacheType' => \get_class($this->authTokenCache),
            'verifierCacheType' => \get_class($this->verifierCache),
        ];
    }

    /**
     * @param array<string, mixed>|null $config
     * @param array<callable(callable): callable>|null $middlewares
     */
    public function createApiClient(?array $config = null, ?array $middlewares = null): Client
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

        $handler = HandlerStack::create();

        if ($this->httpLogMiddleware) {
            $handler->push($this->httpLogMiddleware, 'http_logs');
        }

        if ($this->httpDebugLogMiddleware) {
            $handler->push($this->httpDebugLogMiddleware, 'http_debug_logs');
        }

        if ($middlewares !== null) {
            foreach ($middlewares as $middleware) {
                $handler->push($middleware);
            }
        }

        $credentials = $this->getGoogleAuthTokenCredentials();

        if (!($credentials instanceof FetchAuthTokenInterface) && $this->discoveryIsDisabled) {
            throw new RuntimeException('Unable to create an API client without credentials');
        }

        if ($credentials !== null) {
            $projectId = $credentials instanceof ProjectIdProviderInterface ? $credentials->getProjectId() : $this->getProjectId();
            $cachePrefix = 'kreait_firebase_'.$projectId;

            $credentials = new FetchAuthTokenCache($credentials, ['prefix' => $cachePrefix], $this->authTokenCache);
            $authTokenHandler = HttpHandlerFactory::build(new Client());

            $handler->push(new AuthTokenMiddleware($credentials, $authTokenHandler));
        }

        $handler->push(Middleware::responseWithSubResponses());

        $config['handler'] = $handler;
        $config['auth'] = 'google_auth';

        return new Client($config);
    }

    private function getGoogleAuthTokenCredentials(): ?FetchAuthTokenInterface
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
