<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Google\Cloud\Storage\StorageClient;
use Kreait\Firebase\Storage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
final class StorageTest extends TestCase
{
    #[Test]
    public function itReturnsTheSameClientItWasGiven(): void
    {
        $client = $this->createMock(StorageClient::class);
        $storage = new Storage($client);

        $this->assertSame($client, $storage->getStorageClient());
    }

    #[Test]
    public function itComplainsWhenNoDefaultBucketWasProvided(): void
    {
        $client = $this->createMock(StorageClient::class);
        $storage = new Storage($client);

        $this->expectException(RuntimeException::class);
        $storage->getBucket();
    }
}
