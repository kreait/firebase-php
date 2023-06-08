<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Kreait\Firebase\Auth\AuthResourceUrlBuilder;
use Kreait\Firebase\Util;
use PHPUnit\Framework\TestCase;

use function putenv;

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

    /**
     * @test
     */
    public function itUsesAnEmulatorHostIfProvidedByEnvironmentVariable(): void
    {
        putenv('FIREBASE_AUTH_EMULATOR_HOST=localhost:1234');

        $builder = AuthResourceUrlBuilder::create();

        $this->assertStringContainsString('localhost:1234', $builder->getUrl());
    }

    /**
     * @test
     */
    public function itDoesNotUseTheEmulatorHostWhenItIsEmpty(): void
    {
        putenv('FIREBASE_AUTH_EMULATOR_HOST=');

        $builder = AuthResourceUrlBuilder::create();

        $this->assertStringNotContainsString('{host}', $builder->getUrl());
    }

    /**
     * @test
     */
    public function itReplacesTheApiWithAnEmptyStringWhenItIsNotProvided(): void
    {
        $this->assertStringNotContainsString('{api}', $this->builder->getUrl());
    }

    /**
     * @test
     */
    public function itUsesTheRequestedApi(): void
    {
        $url = $this->builder->getUrl('foo');
        $this->assertStringNotContainsString('{api}', $url);
        $this->assertStringContainsString('foo', $url);
    }

    /**
     * @test
     */
    public function itUsesTheGivenParameters(): void
    {
        $url = $this->builder->getUrl('', ['first' => 'value', 'second' => 'value']);
        $this->assertStringContainsString('?first=value&second=value', $url);
    }

    /**
     * @test
     */
    public function itDoesNotHaveQueryParamsWhenNoneAreProvided(): void
    {
        $url = $this->builder->getUrl();
        $this->assertStringNotContainsString('?', $url);
    }
}
