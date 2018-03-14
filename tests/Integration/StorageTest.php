<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Kreait\Firebase\Storage;
use Kreait\Firebase\Tests\IntegrationTestCase;
use League\Flysystem\FilesystemInterface;

class StorageTest extends IntegrationTestCase
{
    /**
     * @var Storage
     */
    private $storage;

    protected function setUp()
    {
        $this->storage = self::$firebase->getStorage();
    }

    public function testGetStorageClient()
    {
        $this->assertInstanceOf(StorageClient::class, $this->storage->getStorageClient());
    }

    public function testGetBucket()
    {
        $this->assertInstanceOf(Bucket::class, $first = $this->storage->getBucket());
        $this->assertSame($first, $this->storage->getBucket());
    }

    public function testGetCustomBucket()
    {
        $this->assertInstanceOf(Bucket::class, $first = $this->storage->getBucket('custom'));
        $this->assertSame($first, $this->storage->getBucket('custom'));
    }

    public function testGetFilesystem()
    {
        $this->assertInstanceOf(FilesystemInterface::class, $first = $this->storage->getFilesystem());
        $this->assertSame($first, $this->storage->getFilesystem());
    }

    public function testGetCustomFilesystem()
    {
        $this->assertInstanceOf(FilesystemInterface::class, $first = $this->storage->getFilesystem('custom'));
        $this->assertSame($first, $this->storage->getFilesystem('custom'));
    }

    public function testWriteFileOnFilesystem()
    {
        $fs = $this->storage->getFilesystem();
        $path = 'tests/'.uniqid(__METHOD__, true);
        $contents = random_bytes(1);

        $this->assertTrue($fs->put($path, $contents));
        $this->assertSame($contents, $fs->read($path));
        $this->assertTrue($fs->delete($path));
    }
}
