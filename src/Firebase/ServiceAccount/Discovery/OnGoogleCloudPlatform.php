<?php

namespace Kreait\Firebase\ServiceAccount\Discovery;

use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount;
use Kreait\GcpMetadata;

/**
 * @internal
 */
class OnGoogleCloudPlatform
{
    /**
     * @var GcpMetadata
     */
    private $metadata;

    /**
     * @internal
     */
    public function __construct(GcpMetadata $metadata = null)
    {
        $this->metadata = $metadata ?: new GcpMetadata();
    }

    /**
     * @throws ServiceAccountDiscoveryFailed
     *
     * @return ServiceAccount
     */
    public function __invoke(): ServiceAccount
    {
        try {
            return ServiceAccount::withProjectIdAndServiceAccountId(
                $this->metadata->project('project-id'),
                $this->metadata->instance('service-accounts/default/email')
            );
        } catch (GcpMetadata\Error $e) {
            throw new ServiceAccountDiscoveryFailed($e->getMessage());
        }
    }
}
