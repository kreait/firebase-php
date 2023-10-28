<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\StartAt;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class StartAtTest extends UnitTestCase
{
    #[DataProvider('valueProvider')]
    #[Test]
    public function modifyUri(mixed $given, string $expected): void
    {
        $filter = new StartAt($given);

        $this->assertStringContainsString($expected, (string) $filter->modifyUri(new Uri('http://domain.example')));
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function valueProvider(): array
    {
        return [
            'int' => [1, 'startAt=1'],
            'string' => ['value', 'startAt=%22value%22'],
        ];
    }
}
