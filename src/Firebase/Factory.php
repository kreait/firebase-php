<?php

namespace Kreait\Firebase;

use Firebase\Auth\Token\Generator;
use Firebase\Auth\Token\Verifier;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use Google\Cloud\Core\ServiceBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Kreait\Firebase;
use Kreait\Firebase\Http\Middleware;
use Kreait\Firebase\Messaging\MessageFactory;
use Kreait\Firebase\ServiceAccount\Discoverer;
use Psr\Http\Message\UriInterface;
use function GuzzleHttp\Psr7\uri_for;

class Factory
{
    /**
     * @var UriInterface
     */
    private $databaseUri;

    /**
     * @var string
     */
    private $defaultStorageBucket;

    /**
     * @var ServiceAccount
     */
    private $serviceAccount;

    /**
     * @var Discoverer
     */
    private $serviceAccountDiscoverer;

    /**
     * @var string|null
     */
    private $uid;

    /**
     * @var array
     */
    private $claims = [];

    private static $databaseUriPattern = 'https://%s.firebaseio.com';

    private static $storageBucketNamePattern = '%s.appspot.com';

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

    public function asUser(string $uid, array $claims = []): self
    {
        $factory = clone $this;
        $factory->uid = $uid;
        $factory->claims = $claims;

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

    private function getServiceAccountDiscoverer(): Discoverer
    {
        return $this->serviceAccountDiscoverer ?? new Discoverer();
    }

    private function getServiceAccount(): ServiceAccount
    {
        if (!$this->serviceAccount) {
            $this->serviceAccount = $this->getServiceAccountDiscoverer()->discover();
        }

        return $this->serviceAccount;
    }

    private function getDatabaseUri(): UriInterface
    {
        return $this->databaseUri ?: $this->getDatabaseUriFromServiceAccount($this->getServiceAccount());
    }

    private function getStorageBucketName(): string
    {
        return $this->defaultStorageBucket ?: $this->getStorageBucketNameFromServiceAccount($this->getServiceAccount());
    }

    private function getDatabaseUriFromServiceAccount(ServiceAccount $serviceAccount): UriInterface
    {
        return uri_for(sprintf(self::$databaseUriPattern, $serviceAccount->getProjectId()));
    }

    private function getStorageBucketNameFromServiceAccount(ServiceAccount $serviceAccount): string
    {
        return sprintf(self::$storageBucketNamePattern, $serviceAccount->getProjectId());
    }

    private function createAuth(): Auth
    {
        $serviceAccount = $this->getServiceAccount();

        $http = $this->createApiClient($serviceAccount, [
            'base_uri' => 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/',
        ]);

        return new Auth(
            new Auth\ApiClient($http),
            new Generator($serviceAccount->getClientEmail(), $serviceAccount->getPrivateKey()),
            new Verifier($serviceAccount->getProjectId())
        );
    }

    private function createDatabase(): Database
    {
        $http = $this->createApiClient($this->getServiceAccount());

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

    private function createRemoteConfig(): RemoteConfig
    {
        $http = $this->createApiClient($this->getServiceAccount(), [
            'base_uri' => 'https://firebaseremoteconfig.googleapis.com/v1/projects/'.$this->getServiceAccount()->getProjectId().'/remoteConfig',
        ]);

        return new RemoteConfig(new RemoteConfig\ApiClient($http));
    }

    private function createMessaging(): Messaging
    {
        $serviceAccount = $this->getServiceAccount();
        $projectId = $serviceAccount->getProjectId();

        $http = $this->createApiClient($this->getServiceAccount(), [
            'base_uri' => 'https://fcm.googleapis.com/v1/projects/'.$projectId,
        ]);

        return new Messaging(new Messaging\ApiClient($http), new MessageFactory());
    }

    private function createApiClient(ServiceAccount $serviceAccount, array $config = []): Client
    {
        $googleAuthTokenMiddleware = $this->createGoogleAuthTokenMiddleware($serviceAccount);

        $stack = HandlerStack::create();
        $stack->push($googleAuthTokenMiddleware, 'auth_service_account');

        $config = array_merge($config, [
            'handler' => $stack,
            'auth' => 'google_auth',
        ]);

        return new Client($config);
    }

    private function createGoogleAuthTokenMiddleware(ServiceAccount $serviceAccount, array $additionalScopes = []): AuthTokenMiddleware
    {
        $scopes = [
            'https://www.googleapis.com/auth/cloud-platform',
            'https://www.googleapis.com/auth/firebase',
            'https://www.googleapis.com/auth/firebase.messaging',
            'https://www.googleapis.com/auth/firebase.remoteconfig',
            'https://www.googleapis.com/auth/userinfo.email',
        ] + $additionalScopes;

        $credentials = [
            'client_email' => $serviceAccount->getClientEmail(),
            'client_id' => $serviceAccount->getClientId(),
            'private_key' => $serviceAccount->getPrivateKey(),
        ];

        return new AuthTokenMiddleware(new ServiceAccountCredentials($scopes, $credentials));
    }

    private function createStorage(): Storage
    {
        $builder = $this->getGoogleCloudServiceBuilder();

        $storageClient = $builder->storage([
            'projectId' => $this->getServiceAccount()->getProjectId(),
        ]);

        return new Storage($storageClient, $this->getStorageBucketName());
    }

    private function getGoogleCloudServiceBuilder(): ServiceBuilder
    {
        $serviceAccount = $this->getServiceAccount();

        $credentials = [
            'client_email' => $serviceAccount->getClientEmail(),
            'client_id' => $serviceAccount->getClientId(),
            'private_key' => $serviceAccount->getPrivateKey(),
            'type' => 'service_account',
        ];

        return new ServiceBuilder([
            'keyFile' => $credentials,
        ]);
    }
}
