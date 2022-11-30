<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Contract\Storage;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 */
final class StorageTest extends IntegrationTestCase
{
    private Storage $storage;

    protected function setUp(): void
    {
        $this->storage = self::$factory->createStorage();
    }

    public function testItIsConfiguredWithADefaultBucket(): void
    {
        $bucket = $this->storage->getBucket();

        $this->assertTrue($bucket->exists());
    }

    public function testItReturnsANamedBucket(): void
    {
        $bucketName = $this->randomString(__FUNCTION__);
        $bucket = $this->storage->getBucket($bucketName);

        $this->assertSame($bucketName, $bucket->name());
    }

    public function testItCachesBuckets(): void
    {
        $bucketName = $this->randomString(__FUNCTION__);
        $first = $this->storage->getBucket($bucketName);
        $second = $this->storage->getBucket($bucketName);

        $this->assertSame($first, $second);
    }
}
