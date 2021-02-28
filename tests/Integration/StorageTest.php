<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Google\Cloud\Storage\Bucket;
use Kreait\Firebase\Contract\Storage;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 */
class StorageTest extends IntegrationTestCase
{
    /** @var Storage */
    private $storage;

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
        $this->assertInstanceOf(Bucket::class, $first = $this->storage->getBucket());
        $this->assertSame($first, $this->storage->getBucket());
    }

    public function testGetCustomBucket(): void
    {
        $this->assertInstanceOf(Bucket::class, $first = $this->storage->getBucket('custom'));
        $this->assertSame($first, $this->storage->getBucket('custom'));
    }
}
