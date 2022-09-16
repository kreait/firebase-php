<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Google\Cloud\Storage\Bucket;
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

    public function testGetStorageClient(): void
    {
        $this->storage->getStorageClient();
        $this->addToAssertionCount(1);
    }

    public function testGetBucket(): void
    {
        self::assertInstanceOf(Bucket::class, $first = $this->storage->getBucket());
        self::assertSame($first, $this->storage->getBucket());
    }

    public function testGetCustomBucket(): void
    {
        self::assertInstanceOf(Bucket::class, $first = $this->storage->getBucket('custom'));
        self::assertSame($first, $this->storage->getBucket('custom'));
    }
}
