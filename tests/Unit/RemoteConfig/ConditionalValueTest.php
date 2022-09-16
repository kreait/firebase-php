<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\Condition;
use Kreait\Firebase\RemoteConfig\ConditionalValue;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class ConditionalValueTest extends UnitTestCase
{
    public function testCreate(): void
    {
        $condition = Condition::named('my_condition');

        $conditionalValue = ConditionalValue::basedOn($condition)
            ->withValue('foo');

        self::assertSame($condition->name(), $conditionalValue->conditionName());
        self::assertSame('foo', $conditionalValue->value());
        self::assertEquals(['value' => 'foo'], $conditionalValue->jsonSerialize());
    }

    public function testCreateWithString(): void
    {
        $value = ConditionalValue::basedOn('foo');

        self::assertSame('foo', $value->conditionName());
    }
}
