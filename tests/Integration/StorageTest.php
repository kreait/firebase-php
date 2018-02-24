<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Kreait\Firebase\Storage;
use Kreait\Firebase\Tests\IntegrationTestCase;
use League\Flysystem\FilesystemInterface;
use PHPUnit\Framework\TestCase;

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
        $this->assertInstanceOf(Bucket::class, $this->storage->getBucket());
    }

    public function testGetFilesystem()
    {
        $this->assertInstanceOf(FilesystemInterface::class, $this->storage->getFileystem());
    }
}
