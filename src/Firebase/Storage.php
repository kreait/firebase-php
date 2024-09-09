<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Kreait\Firebase\Exception\RuntimeException;

use function array_key_exists;

/**
 * @internal
 */
final class Storage implements Contract\Storage
{
    /**
     * @var Bucket[]
     */
    private array $buckets = [];

    public function __construct(
        private readonly StorageClient $storageClient,
        private readonly ?string $defaultBucket = null,
    ) {
    }

    public function getStorageClient(): StorageClient
    {
        return $this->storageClient;
    }

    public function getBucket(?string $name = null): Bucket
    {
        $name = $name ?: $this->defaultBucket;

        if ($name === null) {
            throw new RuntimeException(
                'No bucket name was given and no default bucked was configured.',
            );
        }

        if (!array_key_exists($name, $this->buckets)) {
            $this->buckets[$name] = $this->storageClient->bucket($name);
        }

        return $this->buckets[$name];
    }
}
