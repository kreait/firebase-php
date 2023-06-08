<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\AppCheck;

use Kreait\Firebase\AppCheck\AppCheckTokenOptions;
use Kreait\Firebase\Exception\AppCheck\InvalidAppCheckTokenOptions;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class AppCheckTokenOptionsTest extends UnitTestCase
{
    #[Test]
    public function fromArrayWithOmittedOptions(): void
    {
        $options = AppCheckTokenOptions::fromArray([]);

        $this->assertNull($options->ttl);
    }

    #[DataProvider('validOptions')]
    #[Test]
    public function fromArrayWithValidOptions(?int $ttl): void
    {
        $options = AppCheckTokenOptions::fromArray([
            'ttl' => $ttl,
        ]);

        $this->assertSame($ttl, $options->ttl);
    }

    #[DataProvider('invalidOptions')]
    #[Test]
    public function fromArrayWithInvalidOptions(int $ttl): void
    {
        $this->expectException(InvalidAppCheckTokenOptions::class);

        AppCheckTokenOptions::fromArray([
            'ttl' => $ttl,
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function validOptions(): array
    {
        return [
            'null' => [null],
            'min-boundary' => [1800],
            'mid-range' => [30240],
            'max-boundary' => [60480],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function invalidOptions(): array
    {
        return [
            'too-small' => [1799],
            'too-large' => [604801],
        ];
    }
}
