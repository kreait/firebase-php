<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Firebase\Auth\Token\Cache\InMemoryCache;
use Firebase\Auth\Token\Domain\Generator;
use Firebase\Auth\Token\Domain\Verifier;
use Firebase\Auth\Token\Generator as CustomTokenGenerator;
use Firebase\Auth\Token\HttpKeyStore;
use Firebase\Auth\Token\Verifier as LegacyIdTokenVerifier;
use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\Credentials\AppIdentityCredentials;
use Google\Auth\Credentials\GCECredentials;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Auth\CredentialsLoader;
use Google\Auth\Middleware\AuthTokenMiddleware;
use Google\Auth\ProjectIdProviderInterface;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Storage\StorageClient;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use function GuzzleHttp\Psr7\uri_for;
use Kreait\Clock;
use Kreait\Clock\SystemClock;
use Kreait\Firebase;
use Kreait\Firebase\Auth\CustomTokenViaGoogleIam;
use Kreait\Firebase\Auth\DisabledLegacyCustomTokenGenerator;
use Kreait\Firebase\Auth\DisabledLegacyIdTokenVerifier;
use Kreait\Firebase\Auth\IdTokenVerifier;
use Kreait\Firebase\Exception\RuntimeException;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\Project\ProjectId;
use Kreait\Firebase\Value\Email;
use Kreait\Firebase\Value\Url;
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
        'https://www.googleapis.com/auth/securetoken',
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
     * @var ServiceAccountCredentials|UserRefreshCredentials|AppIdentityCredentials|GCECredentials|CredentialsLoader|null
     */
    protected $googleAuthTokenCredentials;

    /**
     * @var ProjectId|null
     */
    protected $projectId;

    /**
     * @var Email|null
     */
    protected $clientEmail;

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

    /**
     * @var bool
     */
    protected $discoveryIsDisabled = false;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var string|null
     */
    protected $httpProxy;

    protected static $databaseUriPattern = 'https://%s.firebaseio.com';

    protected static $storageBucketNamePattern = '%s.appspot.com';

    /** @var Clock */
    protected $clock;

    public function __construct()
    {
        $this->clock = new SystemClock();
    }

    public function withServiceAccount($serviceAccount): self
    {
        $serviceAccount = ServiceAccount::fromValue($serviceAccount);

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

    public function withDisabledAutoDiscovery(): self
    {
        $factory = clone $this;
        $factory->discoveryIsDisabled = true;

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

    public function withEnabledDebug(): self
    {
        $factory = clone $this;
        $factory->debug = true;

        return $factory;
    }

    public function withHttpProxy(string $proxy): self
    {
        $factory = clone $this;
        $factory->httpProxy = $proxy;

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

        if ($credentials = \getenv('FIREBASE_CREDENTIALS')) {
            return $this->serviceAccount = ServiceAccount::fromValue($credentials);
        }

        return null;
    }

    /**
     * @return ProjectId|null
     */
    protected function getProjectId()
    {
        if ($this->projectId !== null) {
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

        if ($projectId = \getenv('GOOGLE_CLOUD_PROJECT')) {
            return $this->projectId = ProjectId::fromString((string) $projectId);
        }

        if ($projectId = \getenv('GCLOUD_PROJECT')) {
            return $this->projectId = ProjectId::fromString((string) $projectId);
        }

        return null;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return Email|null
     */
    protected function getClientEmail()
    {
        return $this->clientEmail;
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

    /**
     * @return string|null
     */
    protected function getStorageBucketName()
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
        $apiClient = new Auth\ApiClient($http);

        $customTokenGenerator = $this->createCustomTokenGenerator();
        $idTokenVerifier = $this->createIdTokenVerifier();

        $signInHandler = new Firebase\Auth\SignIn\GuzzleHandler($http);

        return new Auth($apiClient, $customTokenGenerator, $idTokenVerifier, $signInHandler);
    }

    public function createCustomTokenGenerator(): Generator
    {
        $serviceAccount = $this->getServiceAccount();
        $clientEmail = $this->getClientEmail();
        $privateKey = $serviceAccount ? $serviceAccount->getPrivateKey() : '';

        if ($clientEmail && $privateKey !== '') {
            return new CustomTokenGenerator((string) $clientEmail, $privateKey);
        }

        if ($clientEmail) {
            return new CustomTokenViaGoogleIam((string) $clientEmail, $this->createApiClient());
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

        $keyStore = new HttpKeyStore(new Client(), $this->verifierCache ?: new InMemoryCache());

        $baseVerifier = new LegacyIdTokenVerifier($projectId->sanitizedValue(), $keyStore);

        return new IdTokenVerifier($baseVerifier, $this->clock);
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

        $messagingApiClient = new Messaging\ApiClient(
            $this->createApiClient([
                'base_uri' => 'https://fcm.googleapis.com/v1/projects/'.$projectId->value(),
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
     */
    public function createApiClient(array $config = null): Client
    {
        $config = $config ?? [];
        // If present, the config given to this method override fields passed to withHttpClientConfig()
        $config = \array_merge($this->httpClientConfig, $config);

        if ($this->debug) {
            $config['debug'] = true;
        }

        if ($this->httpProxy) {
            $config['proxy'] = $this->httpProxy;
        }

        $handler = $config['handler'] ?? null;

        if (!($handler instanceof HandlerStack)) {
            $handler = HandlerStack::create($handler);
        }

        foreach ($this->httpClientMiddlewares as $middleware) {
            $handler->push($middleware);
        }

        if ($credentials = $this->getGoogleAuthTokenCredentials()) {
            $handler->push(new AuthTokenMiddleware($credentials));
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
            if ($credentials = ApplicationDefaultCredentials::getCredentials(self::API_CLIENT_SCOPES)) {
                return $this->googleAuthTokenCredentials = $credentials;
            }
        } catch (Throwable $e) {
            return null;
        }

        return null;
    }
}
