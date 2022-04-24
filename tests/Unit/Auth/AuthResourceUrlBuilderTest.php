<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Kreait\Firebase\Auth\AuthResourceUrlBuilder;
use Kreait\Firebase\Util;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AuthResourceUrlBuilderTest extends TestCase
{
    private AuthResourceUrlBuilder $builder;

    protected function setUp(): void
    {
        // Make sure the environment variable is not set (just in case it is set by an integration test)
        Util::rmenv('FIREBASE_AUTH_EMULATOR_HOST');

        $this->builder = AuthResourceUrlBuilder::create();
    }

    public function testItUsesAnEmulatorHostIfProvidedByEnvironmentVariable(): void
    {
        \putenv('FIREBASE_AUTH_EMULATOR_HOST=localhost:1234');

        $builder = AuthResourceUrlBuilder::create();

        $this->assertStringContainsString('localhost:1234', $builder->getUrl());
    }

    public function testItDoesNotUseTheEmulatorHostWhenItIsEmpty(): void
    {
        \putenv('FIREBASE_AUTH_EMULATOR_HOST=');

        $builder = AuthResourceUrlBuilder::create();

        $this->assertStringNotContainsString('{host}', $builder->getUrl());
    }

    public function testItReplacesTheApiWithAnEmptyStringWhenItIsNotProvided(): void
    {
        $this->assertStringNotContainsString('{api}', $this->builder->getUrl());
    }

    public function testItUsesTheRequestedApi(): void
    {
        $url = $this->builder->getUrl('foo');
        $this->assertStringNotContainsString('{api}', $url);
        $this->assertStringContainsString('foo', $url);
    }

    public function testItUsesTheGivenParameters(): void
    {
        $url = $this->builder->getUrl('', ['first' => 'value', 'second' => 'value']);
        $this->assertStringContainsString('?first=value&second=value', $url);
    }

    public function testItDoesNotHaveQueryParamsWhenNoneAreProvided(): void
    {
        $url = $this->builder->getUrl();
        $this->assertStringNotContainsString('?', $url);
    }
}
