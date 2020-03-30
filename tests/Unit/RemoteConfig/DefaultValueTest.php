<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\DefaultValue;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class DefaultValueTest extends TestCase
{
    public function testCreateInAppDefaultValue(): void
    {
        $defaultValue = DefaultValue::none();

        $this->assertEquals(['useInAppDefault' => true], $defaultValue->jsonSerialize());
    }

    public function testCreate(): void
    {
        $defaultValue = DefaultValue::with('foo');

        $this->assertEquals(['value' => 'foo'], $defaultValue->jsonSerialize());
    }
}
