<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\RemoteConfig\DefaultValue;
use Kreait\Firebase\RemoteConfig\Parameter;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class ParameterTest extends UnitTestCase
{
    public function testCreateWithImplicitDefaultValue()
    {
        $parameter = Parameter::named('empty');

        $this->assertEquals(DefaultValue::none(), $parameter->defaultValue());
    }

    public function testCreateWithDefaultValue()
    {
        $parameter = Parameter::named('with_default_foo', 'foo');

        $this->assertEquals(DefaultValue::with('foo'), $parameter->defaultValue());
    }

    public function testCreateWithInvalidDefaultValue()
    {
        $this->expectException(InvalidArgumentException::class);
        Parameter::named('invalid', 1);
    }
}
