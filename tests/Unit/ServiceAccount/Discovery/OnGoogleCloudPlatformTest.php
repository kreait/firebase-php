<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\ServiceAccount\Discovery;

use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount\Discovery\OnGoogleCloudPlatform;
use Kreait\GcpMetadata;
use PHPUnit\Framework\TestCase;

class OnGoogleCloudPlatformTest extends TestCase
{
    private $metadata;

    protected function setUp()
    {
        $this->metadata = $this->prophesize(GcpMetadata::class);
    }

    public function testItUsesGcpMetadata()
    {
        $this->metadata->project('project-id')->willReturn('project-id');
        $this->metadata->instance('service-accounts/default/email')->willReturn('email@example.org');

        $discoverer = new OnGoogleCloudPlatform($this->metadata->reveal());

        $serviceAccount = $discoverer();

        $this->assertSame('project-id', $serviceAccount->getProjectId());
        $this->assertSame('email@example.org', $serviceAccount->getClientEmail());
    }

    public function testIfFailsWhenNotOnGcp()
    {
        $discoverer = new OnGoogleCloudPlatform(new GcpMetadata());

        $this->expectException(ServiceAccountDiscoveryFailed::class);
        $discoverer();
    }
}
