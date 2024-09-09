<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Kreait\Firebase\Auth\TenantAwareAuthResourceUrlBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function putenv;

/**
 * @internal
 */
final class TenantAwareAuthResourceUrlBuilderTest extends TestCase
{
    private string $projectId;
    private string $tenantId;
    private TenantAwareAuthResourceUrlBuilder $builder;

    protected function setUp(): void
    {
        // Make sure the environment variable is not set (just in case it is set by an integration test)
        putenv('FIREBASE_AUTH_EMULATOR_HOST');

        $this->projectId = 'my-project';
        $this->tenantId = 'my-tenant';

        $this->builder = TenantAwareAuthResourceUrlBuilder::forProjectAndTenant(
            $this->projectId,
            $this->tenantId,
        );
    }

    #[Test]
    public function itUsesTheGivenProjectAndTenant(): void
    {
        $url = $this->builder->getUrl();

        $this->assertStringNotContainsString('{projectId}', $url);
        $this->assertStringContainsString($this->projectId, $url);
        $this->assertStringNotContainsString('{tenantId}', $url);
        $this->assertStringContainsString($this->tenantId, $url);
    }

    #[Test]
    public function itUsesAnEmulatorHostIfProvidedByEnvironmentVariable(): void
    {
        putenv('FIREBASE_AUTH_EMULATOR_HOST=localhost:1234');

        $builder = TenantAwareAuthResourceUrlBuilder::forProjectAndTenant(
            $this->projectId,
            $this->tenantId,
        );

        $this->assertStringContainsString('localhost:1234', $builder->getUrl());
    }

    #[Test]
    public function itDoesNotUseTheEmulatorHostWhenItIsEmpty(): void
    {
        putenv('FIREBASE_AUTH_EMULATOR_HOST=');

        $builder = TenantAwareAuthResourceUrlBuilder::forProjectAndTenant(
            $this->projectId,
            $this->tenantId,
        );

        $this->assertStringNotContainsString('{host}', $builder->getUrl());
    }

    #[Test]
    public function itReplacesTheApiWithAnEmptyStringWhenItIsNotProvided(): void
    {
        $this->assertStringNotContainsString('{api}', $this->builder->getUrl());
    }

    #[Test]
    public function itUsesTheRequestedApi(): void
    {
        $url = $this->builder->getUrl('foo');
        $this->assertStringNotContainsString('{api}', $url);
        $this->assertStringContainsString('foo', $url);
    }

    #[Test]
    public function itUsesTheGivenParameters(): void
    {
        $url = $this->builder->getUrl('', ['first' => 'value', 'second' => 'value']);
        $this->assertStringContainsString('?first=value&second=value', $url);
    }

    #[Test]
    public function itDoesNotHaveQueryParamsWhenNoneAreProvided(): void
    {
        $url = $this->builder->getUrl();
        $this->assertStringNotContainsString('?', $url);
    }
}
