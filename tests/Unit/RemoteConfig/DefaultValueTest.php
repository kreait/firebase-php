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

        $this->assertTrue($defaultValue->value());
        $this->assertEquals(['useInAppDefault' => true], $defaultValue->jsonSerialize());
    }

    public function testCreate(): void
    {
        $defaultValue = DefaultValue::with('foo');

        $this->assertSame('foo', $defaultValue->value());
        $this->assertEquals(['value' => 'foo'], $defaultValue->jsonSerialize());
    }

    /**
     * @dataProvider arrayValueProvider
     */
    public function testCreateFromArray($expected, array $array): void
    {
        $defaultValue = DefaultValue::fromArray($array);

        $this->assertSame($expected, $defaultValue->value());
    }

    public function arrayValueProvider(): array
    {
        return [
            'inAppDefault' => [
                true,
                ['useInAppDefault' => true],
            ],
            'bool' => [
                true,
                ['value' => true],
            ],
            'string' => [
                'foo',
                ['value' => 'foo'],
            ],
        ];
    }
}
