<?php

namespace Kreait\Firebase\ServiceAccount\Discovery;

use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount;
use Kreait\GcpMetadata;

class OnGoogleCloudPlatform
{
    /**
     * @throws ServiceAccountDiscoveryFailed
     *
     * @return ServiceAccount
     */
    public function __invoke(): ServiceAccount
    {
        $metadata = new GcpMetadata();

        try {
            return ServiceAccount::withProjectIdAndServiceAccountId(
                $metadata->project('project-id'),
                $metadata->instance('service-accounts/default/email')
            );
        } catch (GcpMetadata\Error $e) {
            throw new ServiceAccountDiscoveryFailed($e->getMessage());
        }
    }
}
