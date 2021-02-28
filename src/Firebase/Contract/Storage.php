<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;

interface Storage
{
    public function getStorageClient(): StorageClient;

    public function getBucket(?string $name = null): Bucket;
}
