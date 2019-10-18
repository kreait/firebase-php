<?php

declare(strict_types=1);

namespace Kreait;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Database;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\RemoteConfig;
use Kreait\Firebase\Storage;

/**
 * @deprecated 4.33
 */
class Firebase
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var RemoteConfig
     */
    private $remoteConfig;

    /**
     * @var Messaging
     */
    private $messaging;

    /**
     * @internal
     *
     * @deprecated 4.33
     */
    public function __construct(Database $database, Auth $auth, Storage $storage, RemoteConfig $remoteConfig, Messaging $messaging)
    {
        $this->database = $database;
        $this->auth = $auth;
        $this->storage = $storage;
        $this->remoteConfig = $remoteConfig;
        $this->messaging = $messaging;
    }

    /**
     * @deprecated 4.33 Use {@see \Kreait\Firebase\Factory::createDatabase()} instead
     * @see \Kreait\Firebase\Factory::createDatabase()
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * @deprecated 4.33 Use {@see \Kreait\Firebase\Factory::createAuth()} instead
     * @see \Kreait\Firebase\Factory::createAuth()
     */
    public function getAuth(): Auth
    {
        return $this->auth;
    }

    /**
     * @deprecated 4.33 Use {@see \Kreait\Firebase\Factory::createStorage()} instead
     * @see \Kreait\Firebase\Factory::createStorage()
     */
    public function getStorage(): Storage
    {
        return $this->storage;
    }

    /**
     * @deprecated 4.33 Use {@see \Kreait\Firebase\Factory::createRemoteConfig()} instead
     * @see \Kreait\Firebase\Factory::createRemoteConfig()
     */
    public function getRemoteConfig(): RemoteConfig
    {
        return $this->remoteConfig;
    }

    /**
     * @deprecated 4.33 Use {@see \Kreait\Firebase\Factory::createMessaging()} instead
     * @see \Kreait\Firebase\Factory::createMessaging()
     */
    public function getMessaging(): Messaging
    {

        return $this->messaging;
    }
}
