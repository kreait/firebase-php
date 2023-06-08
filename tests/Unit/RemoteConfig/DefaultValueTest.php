<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\RemoteConfig;

use Kreait\Firebase\RemoteConfig\DefaultValue;
use Kreait\Firebase\RemoteConfig\ExplicitValue;
use Kreait\Firebase\RemoteConfig\PersonalizationValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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
     * @param RemoteConfigInAppDefaultValueShape $expected
     * @param RemoteConfigInAppDefaultValueShape $data
     */
    #[DataProvider('arrayValueProvider')]
    #[Test]
    public function createFromArray(array $expected, array $data): void
    {
        $defaultValue = DefaultValue::fromArray($data);

        $this->assertSame($expected, $defaultValue->toArray());
    }

    /**
     * @return iterable<non-empty-string, array<RemoteConfigInAppDefaultValueShape|RemoteConfigExplicitValueShape|RemoteConfigPersonalizationValueShape>>
     */
    public static function arrayValueProvider(): iterable
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
