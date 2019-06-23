<?php

namespace Kreait\Firebase\ServiceAccount\Discovery;

use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount;

/**
 * @internal
 */
class FromPath
{
    /**
     * @var string
     */
    private $path;

    /**
     * @internal
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @throws ServiceAccountDiscoveryFailed
     *
     * @return ServiceAccount
     */
    public function __invoke(): ServiceAccount
    {
        try {
            return ServiceAccount::fromJsonFile($this->path);
        } catch (\Throwable $e) {
            throw new ServiceAccountDiscoveryFailed($e->getMessage());
        }
    }
}
