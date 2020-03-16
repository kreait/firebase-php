<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;

class Storage
{
    /**
     * @var StorageClient
     */
    private $storageClient;

    /**
     * @var string
     */
    private $defaultBucket;

    /**
     * @var Bucket[]
     */
    private $buckets = [];

    /**
     * @internal
     */
    public function __construct(StorageClient $storageClient, string $defaultBucket)
    {
        $this->storageClient = $storageClient;
        $this->defaultBucket = $defaultBucket;
    }

    public function getStorageClient(): StorageClient
    {
        return $this->storageClient;
    }

    public function getBucket(string $name = null): Bucket
    {
        $name = $name ?: $this->defaultBucket;

        if (!\array_key_exists($name, $this->buckets)) {
            $this->buckets[$name] = $this->storageClient->bucket($name);
        }

        return $this->buckets[$name];
    }
}
