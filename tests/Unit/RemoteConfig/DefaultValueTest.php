<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\DefaultValue;
use PHPUnit\Framework\TestCase;

class DefaultValueTest extends TestCase
{
    public function testCreateInAppDefaultValue()
    {
        $defaultValue = DefaultValue::none();

        $this->assertEquals(['useInAppDefault' => true], $defaultValue->jsonSerialize());
    }

    public function testCreate()
    {
        $defaultValue = DefaultValue::with('foo');

        $this->assertEquals(['value' => 'foo'], $defaultValue->jsonSerialize());
    }
}
