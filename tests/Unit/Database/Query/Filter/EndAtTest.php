<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\EndAt;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class EndAtTest extends UnitTestCase
{
    #[DataProvider('valueProvider')]
    #[Test]
    public function modifyUri(mixed $given, string $expected): void
    {
        $filter = new EndAt($given);

        $this->assertStringContainsString($expected, (string) $filter->modifyUri(new Uri('http://example.com')));
    }

    public static function valueProvider(): \Iterator
    {
        yield 'int' => [1, 'endAt=1'];
        yield 'string' => ['value', 'endAt=%22value%22'];
    }
}
