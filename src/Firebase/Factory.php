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
use function GuzzleHttp\Psr7\uri_for;
use GuzzleHttp\RequestOptions;
use Kreait\Clock;
use Kreait\Clock\SystemClock;
use Kreait\Firebase;
use Kreait\Firebase\Auth\CustomTokenViaGoogleIam;
use Kreait\Firebase\Auth\DisabledLegacyCustomTokenGenerator;
use Kreait\Firebase\Auth\DisabledLegacyIdTokenVerifier;
use Kreait\Firebase\Auth\IdTokenVerifier;
use Kreait\Firebase\Auth\TenantId;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Exception\RuntimeException;
use Kreait\Firebase\Http\HttpClientOptions;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\Project\ProjectId;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\Url;
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

    /** @var UriInterface|null */
    protected $databaseUri;

    /** @var string|null */
    protected $defaultStorageBucket;

    /** @var ServiceAccount|null */
    protected $serviceAccount;

    /** @var ServiceAccountCredentials|UserRefreshCredentials|AppIdentityCredentials|GCECredentials|CredentialsLoader|null */
    protected $googleAuthTokenCredentials;

    /** @var ProjectId|null */
    protected $projectId;

    /** @var Email|null */
    protected $clientEmail;

    /** @var CacheInterface */
    protected $verifierCache;

    /** @var CacheItemPoolInterface */
    protected $authTokenCache;

    /** @var bool */
    protected $discoveryIsDisabled = false;

    /** @var bool */
    protected $guzzleDebugModeIsEnabled = false;

    /**
     * @var string|null
     *
     * @deprecated 5.7.0 Use {@see withClientOptions} instead.
     */
    protected $httpProxy;

    /** @var string */
    protected static $databaseUriPattern = 'https://%s.firebaseio.com';

    /** @var string */
    protected static $storageBucketNamePattern = '%s.appspot.com';

    /** @var Clock */
    protected $clock;

    /** @var callable|null */
    protected $httpLogMiddleware;

    /** @var callable|null */
    protected $httpDebugLogMiddleware;

    /** @var TenantId|null */
    protected $tenantId;

    /** @var HttpClientOptions */
    protected $httpClientOptions;

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
            ->withClientEmail($serviceAccount->getClientEmail());
    }

    public function withProjectId(string $projectId): self
    {
        $factory = clone $this;
        $factory->projectId = ProjectId::fromString($projectId);

        return $factory;
    }

    public function withClientEmail(string $clientEmail): self
    {
        $factory = clone $this;
        $factory->clientEmail = new Email($clientEmail);

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
        $factory->databaseUri = uri_for($uri);

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

    public function withEnabledDebug(?LoggerInterface $logger = null): self
    {
        $factory = clone $this;

        if ($logger) {
            $factory = $factory->withHttpDebugLogger($logger);
        } else {
            Firebase\Util\Deprecation::trigger(__METHOD__.' without a '.LoggerInterface::class);
            // @codeCoverageIgnoreStart
            $factory->guzzleDebugModeIsEnabled = true;
            // @codeCoverageIgnoreEnd
        }

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

    public function withHttpProxy(string $proxy): self
    {
        $factory = $this->withHttpClientOptions(
            $this->httpClientOptions->withProxy($proxy)
        );

        $factory->httpProxy = $factory->httpClientOptions->proxy();

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
        if ($this->serviceAccount) {
            return $this->serviceAccount;
        }

        if ($credentials = Util::getenv('FIREBASE_CREDENTIALS')) {
            return $this->serviceAccount = ServiceAccount::fromValue($credentials);
        }

        if ($this->discoveryIsDisabled) {
            return null;
        }

        if ($credentials = Util::getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            try {
                return $this->serviceAccount = ServiceAccount::fromValue($credentials);
            } catch (InvalidArgumentException $e) {
                // Do nothing, continue trying
            }
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

    protected function getProjectId(): ?ProjectId
    {
        if ($this->projectId) {
            return $this->projectId;
        }

        if ($serviceAccount = $this->getServiceAccount()) {
            return $this->projectId = ProjectId::fromString($serviceAccount->getProjectId());
        }

        if ($this->discoveryIsDisabled) {
            return null;
        }

        if (
            ($credentials = $this->getGoogleAuthTokenCredentials())
            && ($credentials instanceof ProjectIdProviderInterface)
            && ($projectId = $credentials->getProjectId())
        ) {
            return $this->projectId = ProjectId::fromString($projectId);
        }

        if ($projectId = Util::getenv('GOOGLE_CLOUD_PROJECT')) {
            return $this->projectId = ProjectId::fromString((string) $projectId);
        }

        if ($projectId = Util::getenv('GCLOUD_PROJECT')) {
            return $this->projectId = ProjectId::fromString((string) $projectId);
        }

        return null;
    }

    protected function getClientEmail(): ?Email
    {
        if ($this->clientEmail) {
            return $this->clientEmail;
        }

        if ($serviceAccount = $this->getServiceAccount()) {
            return $this->clientEmail = new Email($serviceAccount->getClientEmail());
        }

        if ($this->discoveryIsDisabled) {
            return null;
        }

        if (
            ($credentials = $this->getGoogleAuthTokenCredentials())
            && ($credentials instanceof SignBlobInterface)
            && ($clientEmail = $credentials->getClientName())
        ) {
            return $this->clientEmail = new Email($clientEmail);
        }

        return null;
    }

    protected function getDatabaseUri(): UriInterface
    {
        if ($this->databaseUri) {
            return $this->databaseUri;
        }

        if ($projectId = $this->getProjectId()) {
            return $this->databaseUri = uri_for(\sprintf(self::$databaseUriPattern, $projectId->sanitizedValue()));
        }

        throw new RuntimeException('Unable to build a database URI without a project ID');
    }

    protected function getStorageBucketName(): ?string
    {
        if ($this->defaultStorageBucket) {
            return $this->defaultStorageBucket;
        }

        if ($projectId = $this->getProjectId()) {
            return $this->defaultStorageBucket = \sprintf(self::$storageBucketNamePattern, $projectId->sanitizedValue());
        }

        return null;
    }

    public function createAuth(): Auth
    {
        $http = $this->createApiClient([
            'base_uri' => 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/',
        ]);

        $apiClient = new Auth\ApiClient($http, $this->tenantId);

        $customTokenGenerator = $this->createCustomTokenGenerator();

        $idTokenVerifier = $this->createIdTokenVerifier();

        $signInHandler = new Firebase\Auth\SignIn\GuzzleHandler($http);

        return new Auth($apiClient, $customTokenGenerator, $idTokenVerifier, $signInHandler, $this->tenantId);
    }

    public function createCustomTokenGenerator(): Generator
    {
        $serviceAccount = $this->getServiceAccount();
        $clientEmail = $this->getClientEmail();
        $privateKey = $serviceAccount ? $serviceAccount->getPrivateKey() : '';

        if ($clientEmail && $privateKey !== '') {
            if ($this->tenantId) {
                return new TenantAwareGenerator($this->tenantId->toString(), (string) $clientEmail, $privateKey);
            }

            return new CustomTokenGenerator((string) $clientEmail, $privateKey);
        }

        if ($clientEmail) {
            return new CustomTokenViaGoogleIam((string) $clientEmail, $this->createApiClient(), $this->tenantId);
        }

        return new DisabledLegacyCustomTokenGenerator(
            'Custom Token Generation is disabled because the current credentials do not permit it'
        );
    }

    public function createIdTokenVerifier(): Verifier
    {
        if (!($projectId = $this->getProjectId())) {
            return new DisabledLegacyIdTokenVerifier(
                'ID Token Verification is disabled because no project ID was provided'
            );
        }

        $keyStore = new HttpKeyStore(new Client(), $this->verifierCache);

        $baseVerifier = new LegacyIdTokenVerifier($projectId->sanitizedValue(), $keyStore);

        if ($this->tenantId) {
            $baseVerifier = new TenantAwareVerifier($this->tenantId->toString(), $baseVerifier);
        }

        return new IdTokenVerifier($baseVerifier, $this->clock);
    }

    public function createDatabase(): Database
    {
        $http = $this->createApiClient();

        /** @var HandlerStack $handler */
        $handler = $http->getConfig('handler');
        $handler->push(Firebase\Http\Middleware::ensureJsonSuffix(), 'realtime_database_json_suffix');

        return new Database($this->getDatabaseUri(), new Database\ApiClient($http));
    }

    public function createRemoteConfig(): RemoteConfig
    {
        if (!($projectId = $this->getProjectId())) {
            throw new RuntimeException('Unable to create the messaging service without a project ID');
        }

        $http = $this->createApiClient([
            'base_uri' => "https://firebaseremoteconfig.googleapis.com/v1/projects/{$projectId->value()}/remoteConfig",
        ]);

        return new RemoteConfig(new RemoteConfig\ApiClient($http));
    }

    public function createMessaging(): Messaging
    {
        if (!($projectId = $this->getProjectId())) {
            throw new RuntimeException('Unable to create the messaging service without a project ID');
        }

        $errorHandler = new MessagingApiExceptionConverter($this->clock);

        $messagingApiClient = new Messaging\ApiClient(
            $this->createApiClient([
                'base_uri' => 'https://fcm.googleapis.com/v1/projects/'.$projectId->value(),
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

    public function createFirestore(): Firestore
    {
        $config = [];

        if ($serviceAccount = $this->getServiceAccount()) {
            $config['keyFile'] = $serviceAccount->asArray();
        } elseif ($this->discoveryIsDisabled) {
            throw new RuntimeException('Unable to create a Firestore Client without credentials');
        }

        if ($projectId = $this->getProjectId()) {
            $config['projectId'] = $projectId->value();
        }

        if (!$projectId) {
            // This is the case with user refresh credentials
            $config['suppressKeyFileNotice'] = true;
        }

        try {
            $firestoreClient = new FirestoreClient($config);
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to create a FirestoreClient: '.$e->getMessage(), $e->getCode(), $e);
        }

        return Firestore::withFirestoreClient($firestoreClient);
    }

    public function createStorage(): Storage
    {
        $config = [];

        if ($serviceAccount = $this->getServiceAccount()) {
            $config['keyFile'] = $serviceAccount->asArray();
        } elseif ($this->discoveryIsDisabled) {
            throw new RuntimeException('Unable to create a Storage Client without credentials');
        }

        if ($projectId = $this->getProjectId()) {
            $config['projectId'] = $projectId->value();
        }

        if (!$projectId) {
            // This is the case with user refresh credentials
            $config['suppressKeyFileNotice'] = true;
        }

        try {
            $storageClient = new StorageClient($config);
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to create a Storage Client: '.$e->getMessage(), $e->getCode(), $e);
        }

        return new Storage($storageClient, $this->getStorageBucketName());
    }

    /**
     * @internal
     *
     * @param array<string, mixed>|null $config
     */
    public function createApiClient(?array $config = null): Client
    {
        $config = $config ?? [];

        // @codeCoverageIgnoreStart
        if ($this->guzzleDebugModeIsEnabled) {
            $config[RequestOptions::DEBUG] = true;
        }
        // @codeCoverageIgnoreEnd

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

        if ($handler instanceof HandlerStack) {
            if ($this->httpLogMiddleware) {
                $handler->push($this->httpLogMiddleware, 'http_logs');
            }

            if ($this->httpDebugLogMiddleware) {
                $handler->push($this->httpDebugLogMiddleware, 'http_debug_logs');
            }
        }

        if ($credentials = $this->getGoogleAuthTokenCredentials()) {
            $credentials = new FetchAuthTokenCache($credentials, null, $this->authTokenCache);
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

    /**
     * @return ServiceAccountCredentials|UserRefreshCredentials|AppIdentityCredentials|GCECredentials|CredentialsLoader|null
     */
    protected function getGoogleAuthTokenCredentials()
    {
        if ($this->googleAuthTokenCredentials) {
            return $this->googleAuthTokenCredentials;
        }

        if ($serviceAccount = $this->getServiceAccount()) {
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
