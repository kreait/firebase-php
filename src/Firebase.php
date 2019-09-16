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
        \trigger_error(
            __METHOD__.' is deprecated. Use \Kreait\Firebase\Factory::createDatabase() instead.',
            \E_USER_DEPRECATED
        );

        return $this->database;
    }

    /**
     * @deprecated 4.33 Use {@see \Kreait\Firebase\Factory::createAuth()} instead
     * @see \Kreait\Firebase\Factory::createAuth()
     */
    public function getAuth(): Auth
    {
        \trigger_error(
            __METHOD__.' is deprecated. Use \Kreait\Firebase\Factory::createAuth() instead.',
            \E_USER_DEPRECATED
        );

        return $this->auth;
    }

    /**
     * @deprecated 4.33 Use {@see \Kreait\Firebase\Factory::createStorage()} instead
     * @see \Kreait\Firebase\Factory::createStorage()
     */
    public function getStorage(): Storage
    {
        \trigger_error(
            __METHOD__.' is deprecated. Use \Kreait\Firebase\Factory::createStorage() instead.',
            \E_USER_DEPRECATED
        );

        return $this->storage;
    }

    /**
     * @deprecated 4.33 Use {@see \Kreait\Firebase\Factory::createRemoteConfig()} instead
     * @see \Kreait\Firebase\Factory::createRemoteConfig()
     */
    public function getRemoteConfig(): RemoteConfig
    {
        \trigger_error(
            __METHOD__.' is deprecated. Use \Kreait\Firebase\Factory::createRemoteConfig() instead.',
            \E_USER_DEPRECATED
        );

        return $this->remoteConfig;
    }

    /**
     * @deprecated 4.33 Use {@see \Kreait\Firebase\Factory::createMessaging()} instead
     * @see \Kreait\Firebase\Factory::createMessaging()
     */
    public function getMessaging(): Messaging
    {
        \trigger_error(
            __METHOD__.' is deprecated. Use \Kreait\Firebase\Factory::createMessaging() instead.',
            \E_USER_DEPRECATED
        );

        return $this->messaging;
    }
}
