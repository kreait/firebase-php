<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\DefaultValue;
use Kreait\Firebase\RemoteConfig\Parameter;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class ParameterTest extends UnitTestCase
{
    public function testCreateWithImplicitDefaultValue(): void
    {
        $parameter = Parameter::named('empty');

        $this->assertNull($parameter->defaultValue());
    }

    public function testCreateWithDefaultValue(): void
    {
        $parameter = Parameter::named('with_default_foo', 'foo');

        $this->assertEquals(DefaultValue::with('foo'), $parameter->defaultValue());
    }

    public function testCreateWithDescription(): void
    {
        $parameter = Parameter::named('something')->withDescription('description');

        $this->assertSame('description', $parameter->description());
    }
}
