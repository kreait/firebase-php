<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

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
     * @var FilesystemInterface[]
     */
    private $filesystems = [];

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

    /**
     * @deprecated 4.33
     */
    public function getFilesystem(string $bucketName = null): FilesystemInterface
    {
        \trigger_error(__METHOD__.' is deprecated.', \E_USER_DEPRECATED);

        $bucket = $this->getBucket($bucketName);

        if (!\array_key_exists($name = $bucket->name(), $this->filesystems)) {
            $adapter = new GoogleStorageAdapter($this->storageClient, $bucket);
            $this->filesystems[$name] = new Filesystem($adapter);
        }

        return $this->filesystems[$name];
    }
}
