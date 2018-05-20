<?php

namespace Kreait;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Database;
use Kreait\Firebase\Firestore;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\RemoteConfig;
use Kreait\Firebase\Storage;

class Firebase
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var Firestore
     */
    private $firestore;

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

    public function __construct(Database $database, Firestore $firestore, Auth $auth, Storage $storage, RemoteConfig $remoteConfig, Messaging $messaging)
    {
        $this->database = $database;
        $this->firestore = $firestore;
        $this->auth = $auth;
        $this->storage = $storage;
        $this->remoteConfig = $remoteConfig;
        $this->messaging = $messaging;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function getFirestore(): Firestore
    {
        return $this->firestore;
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

    public function getMessaging(): Messaging
    {
        return $this->messaging;
    }
}
