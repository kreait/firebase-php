<?php

namespace Kreait;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Database;
use Kreait\Firebase\RemoteConfig;
use Kreait\Firebase\Storage;

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

    public function __construct(Database $database, Auth $auth, Storage $storage, RemoteConfig $remoteConfig)
    {
        $this->database = $database;
        $this->auth = $auth;
        $this->storage = $storage;
        $this->remoteConfig = $remoteConfig;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function getAuth(): Auth
    {
        return $this->auth;
    }

    public function getStorage(): Storage
    {
        return $this->storage;
    }

    public function getRemoteConfig(): RemoteConfig
    {
        return $this->remoteConfig;
    }
}
