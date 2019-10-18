<?php

declare(strict_types=1);

namespace Kreait;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Firestore;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\RemoteConfig;
use Kreait\Firebase\Storage;

/**
 * @deprecated 4.33
 */
class Firebase
{
    /** @var Factory */
    private $factory;

    /**
     * @internal
     *
     * @deprecated 4.33
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @deprecated 4.33 Use {@see \Kreait\Firebase\Factory::createDatabase()} instead
     * @see \Kreait\Firebase\Factory::createDatabase()
     */
    public function getDatabase(): Database
    {
        return $this->factory->createDatabase();
    }

    /**
     * @deprecated 4.33 Use {@see \Kreait\Firebase\Factory::createAuth()} instead
     * @see \Kreait\Firebase\Factory::createAuth()
     */
    public function getAuth(): Auth
    {
        return $this->factory->createAuth();
    }

    /**
     * @deprecated 4.33 Use {@see \Kreait\Firebase\Factory::createStorage()} instead
     * @see \Kreait\Firebase\Factory::createStorage()
     */
    public function getStorage(): Storage
    {
        return $this->factory->createStorage();
    }

    /**
     * @deprecated 4.33 Use {@see \Kreait\Firebase\Factory::createRemoteConfig()} instead
     * @see \Kreait\Firebase\Factory::createRemoteConfig()
     */
    public function getRemoteConfig(): RemoteConfig
    {
        return $this->factory->createRemoteConfig();
    }

    /**
     * @deprecated 4.33 Use {@see \Kreait\Firebase\Factory::createMessaging()} instead
     * @see \Kreait\Firebase\Factory::createMessaging()
     */
    public function getMessaging(): Messaging
    {
        return $this->factory->createMessaging();
    }

    /**
     * @deprecated 4.35 Use {@see \Kreait\Firebase\Factory::createFirestore()} instead
     * @see \Kreait\Firebase\Factory::createFirestore()
     */
    public function getFirestore(): Firestore
    {
        return $this->factory->createFirestore();
    }
}
