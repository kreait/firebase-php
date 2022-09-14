<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\DefaultValue;
use Kreait\Firebase\RemoteConfig\ExplicitValue;
use Kreait\Firebase\RemoteConfig\PersonalizationValue;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @phpstan-import-type RemoteConfigPersonalizationValueShape from PersonalizationValue
 * @phpstan-import-type RemoteConfigExplicitValueShape from ExplicitValue
 * @phpstan-import-type RemoteConfigInAppDefaultValueShape from DefaultValue
 */
final class DefaultValueTest extends TestCase
{
    public function testCreateInAppDefaultValue(): void
    {
        $defaultValue = DefaultValue::useInAppDefault();

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
     * @param RemoteConfigInAppDefaultValueShape $expected
     * @param RemoteConfigInAppDefaultValueShape $data
     */
    public function testCreateFromArray(array $expected, array $data): void
    {
        $defaultValue = DefaultValue::fromArray($data);

        $this->assertSame($expected, $defaultValue->toArray());
    }

    /**
     * @return iterable<non-empty-string, array<RemoteConfigInAppDefaultValueShape|RemoteConfigExplicitValueShape|RemoteConfigPersonalizationValueShape>>
     */
    public function arrayValueProvider(): iterable
    {
        yield 'inAppDefault' => [
            ['useInAppDefault' => true],
            ['useInAppDefault' => true],
        ];

        yield 'explicit' => [
            ['value' => '1'],
            ['value' => '1'],
        ];

        yield 'personalization' => [
            ['personalizationId' => 'pid'],
            ['personalizationId' => 'pid'],
        ];
    }
}
