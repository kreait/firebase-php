<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\EndBefore;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class EndBeforeTest extends UnitTestCase
{
    /**
     * @dataProvider valueProvider
     *
     * @test
     */
    public function modifyUri(mixed $given, string $expected): void
    {
        $filter = new EndBefore($given);

        $this->assertStringContainsString($expected, (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function valueProvider(): array
    {
        return [
            'int' => [1, 'endBefore=1'],
            'string' => ['value', 'endBefore=%22value%22'],
        ];
    }
}
