<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Kreait\Firebase\Auth\TenantAwareAuthResourceUrlBuilder;
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

    public function testItUsesTheGivenProjectAndTenant(): void
    {
        $url = $this->builder->getUrl();

        self::assertStringNotContainsString('{projectId}', $url);
        self::assertStringContainsString($this->projectId, $url);
        self::assertStringNotContainsString('{tenantId}', $url);
        self::assertStringContainsString($this->tenantId, $url);
    }

    public function testItUsesAnEmulatorHostIfProvidedByEnvironmentVariable(): void
    {
        putenv('FIREBASE_AUTH_EMULATOR_HOST=localhost:1234');

        $builder = TenantAwareAuthResourceUrlBuilder::forProjectAndTenant(
            $this->projectId,
            $this->tenantId,
        );

        self::assertStringContainsString('localhost:1234', $builder->getUrl());
    }

    public function testItDoesNotUseTheEmulatorHostWhenItIsEmpty(): void
    {
        putenv('FIREBASE_AUTH_EMULATOR_HOST=');

        $builder = TenantAwareAuthResourceUrlBuilder::forProjectAndTenant(
            $this->projectId,
            $this->tenantId,
        );

        self::assertStringNotContainsString('{host}', $builder->getUrl());
    }

    public function testItReplacesTheApiWithAnEmptyStringWhenItIsNotProvided(): void
    {
        self::assertStringNotContainsString('{api}', $this->builder->getUrl());
    }

    public function testItUsesTheRequestedApi(): void
    {
        $url = $this->builder->getUrl('foo');
        self::assertStringNotContainsString('{api}', $url);
        self::assertStringContainsString('foo', $url);
    }

    public function testItUsesTheGivenParameters(): void
    {
        $url = $this->builder->getUrl('', ['first' => 'value', 'second' => 'value']);
        self::assertStringContainsString('?first=value&second=value', $url);
    }

    public function testItDoesNotHaveQueryParamsWhenNoneAreProvided(): void
    {
        $url = $this->builder->getUrl();
        self::assertStringNotContainsString('?', $url);
    }
}
