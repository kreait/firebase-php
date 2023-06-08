<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\Condition;
use Kreait\Firebase\RemoteConfig\ConditionalValue;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class ConditionalValueTest extends UnitTestCase
{
    #[Test]
    public function create(): void
    {
        $condition = Condition::named('my_condition');

        $conditionalValue = ConditionalValue::basedOn($condition)
            ->withValue('foo')
        ;

        $this->assertSame($condition->name(), $conditionalValue->conditionName());
        $this->assertSame('foo', $conditionalValue->value());
        $this->assertEqualsCanonicalizing(['value' => 'foo'], $conditionalValue->jsonSerialize());
    }

    #[Test]
    public function createWithString(): void
    {
        $value = ConditionalValue::basedOn('foo');

        $this->assertSame('foo', $value->conditionName());
    }
}
