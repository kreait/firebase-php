<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\DefaultValue;
use Kreait\Firebase\RemoteConfig\ParameterValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @phpstan-import-type RemoteConfigParameterValueShape from ParameterValue
 */
final class DefaultValueTest extends TestCase
{
    #[Test]
    public function createInAppDefaultValue(): void
    {
        $defaultValue = DefaultValue::useInAppDefault();

        $this->assertEqualsCanonicalizing(['useInAppDefault' => true], $defaultValue->jsonSerialize());
    }

    #[Test]
    public function create(): void
    {
        $defaultValue = DefaultValue::with('foo');

        $this->assertEqualsCanonicalizing(['value' => 'foo'], $defaultValue->jsonSerialize());
    }

    /**
     * @param RemoteConfigParameterValueShape $expected
     * @param RemoteConfigParameterValueShape $data
     */
    #[DataProvider('arrayValueProvider')]
    #[Test]
    public function createFromArray(array $expected, array $data): void
    {
        $defaultValue = DefaultValue::fromArray($data);

        $this->assertSame($expected, $defaultValue->toArray());
    }

    /**
     * @return iterable<non-empty-string, array<RemoteConfigParameterValueShape>>
     */
    public static function arrayValueProvider(): iterable
    {
        yield 'inAppDefault' => [
            ['useInAppDefault' => true],
            ['useInAppDefault' => true],
        ];

        yield 'explicit' => [
            ['value' => 'value'],
            ['value' => 'value'],
        ];

        yield 'personalization' => [
            ['personalizationValue' => ['personalizationId' => 'pid']],
            ['personalizationValue' => ['personalizationId' => 'pid']],
        ];
    }
}
