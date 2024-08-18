<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\StartAfter;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class StartAfterTest extends UnitTestCase
{
    #[DataProvider('valueProvider')]
    #[Test]
    public function modifyUri(mixed $given, mixed $expected): void
    {
        $filter = new StartAfter($given);

        $this->assertStringContainsString($expected, (string) $filter->modifyUri(new Uri('http://example.com')));
    }

    public static function valueProvider(): \Iterator
    {
        yield 'int' => [1, 'startAfter=1'];
        yield 'string' => ['value', 'startAfter=%22value%22'];
    }
}
