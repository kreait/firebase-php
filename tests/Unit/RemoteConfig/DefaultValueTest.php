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
     *
     * @param bool|string $expected
     * @param array{
     *     value: string|bool
     * }|array{
     *     useInAppDefault: bool
     * } $data
     */
    public function testCreateFromArray($expected, array $data): void
    {
        $defaultValue = DefaultValue::fromArray($data);

        $this->assertSame($expected, $defaultValue->value());
    }

    /**
     * @return iterable<array{value?: string|bool, useInAppDefault?: bool}>
     */
    public function arrayValueProvider()
    {
        yield 'inAppDefault' => [true, ['useInAppDefault' => true]];
        yield 'bool' => [true, ['value' => true]];
        yield 'string' => ['foo', ['value' => 'foo']];
    }
}
