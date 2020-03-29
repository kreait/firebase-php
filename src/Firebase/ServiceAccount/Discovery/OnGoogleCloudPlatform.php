<?php

declare(strict_types=1);

namespace Kreait\Firebase\ServiceAccount\Discovery;

use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount;
use Kreait\GcpMetadata;

/**
 * @internal
 *
 * @deprecated 4.42
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
     */
    public function __invoke(): ServiceAccount
    {
        try {
            return ServiceAccount::fromValue([
                'type' => 'service_account',
                'project_id' => $this->metadata->project('project-id'),
                'client_email' => $this->metadata->instance('service-accounts/default/email'),
            ]);
        } catch (GcpMetadata\Error $e) {
            throw new ServiceAccountDiscoveryFailed($e->getMessage());
        }
    }
}
