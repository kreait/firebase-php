<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

interface StorageProvider
{
    public function storage(): Storage;
}
