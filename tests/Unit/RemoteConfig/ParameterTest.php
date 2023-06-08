<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\DefaultValue;
use Kreait\Firebase\RemoteConfig\Parameter;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class ParameterTest extends UnitTestCase
{
    #[Test]
    public function createWithImplicitDefaultValue(): void
    {
        $parameter = Parameter::named('empty');

        $this->assertNull($parameter->defaultValue());
    }

    #[Test]
    public function createWithDefaultValue(): void
    {
        $parameter = Parameter::named('with_default_foo', 'foo');

        $this->assertEqualsCanonicalizing(DefaultValue::with('foo')->toArray(), $parameter->defaultValue()?->toArray());
    }

    #[Test]
    public function createWithDescription(): void
    {
        $parameter = Parameter::named('something')->withDescription('description');

        $this->assertSame('description', $parameter->description());
    }
}
