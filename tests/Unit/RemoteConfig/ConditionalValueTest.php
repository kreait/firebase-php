<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\Condition;
use Kreait\Firebase\RemoteConfig\ConditionalValue;
use Kreait\Firebase\Tests\UnitTestCase;

class ConditionalValueTest extends UnitTestCase
{
    public function testCreate()
    {
        $condition = Condition::named('my_condition');

        $conditionalValue = ConditionalValue::basedOn($condition)
            ->withValue('foo');

        $this->assertSame($condition->name(), $conditionalValue->conditionName());
        $this->assertSame('foo', $conditionalValue->value());
        $this->assertEquals(['value' => 'foo'], $conditionalValue->jsonSerialize());
    }
}
