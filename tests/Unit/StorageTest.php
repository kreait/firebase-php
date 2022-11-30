<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Google\Cloud\Storage\StorageClient;
use Kreait\Firebase\Storage;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
final class StorageTest extends TestCase
{
    public function testItReturnsTheSameClientItWasGiven(): void
    {
        $client = $this->createMock(StorageClient::class);
        $storage = new Storage($client);

        $this->assertSame($client, $storage->getStorageClient());
    }

    public function testItComplainsWhenNoDefaultBucketWasProvided(): void
    {
        $client = $this->createMock(StorageClient::class);
        $storage = new Storage($client);

        $this->expectException(RuntimeException::class);
        $storage->getBucket();
    }
}
