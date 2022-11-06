<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\StartAfter;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class StartAfterTest extends UnitTestCase
{
    public function testCreateWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new StartAfter(null);
    }

    /**
     * @dataProvider valueProvider
     */
    public function testModifyUri(mixed $given, mixed $expected): void
    {
        $filter = new StartAfter($given);

        $this->assertStringContainsString($expected, (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public function valueProvider()
    {
        return [
            'int' => [1, 'startAfter=1'],
            'string' => ['value', 'startAfter=%22value%22'],
        ];
    }
}
