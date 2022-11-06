<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\EndAt;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class EndAtTest extends UnitTestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function testModifyUri(mixed $given, string $expected): void
    {
        $filter = new EndAt($given);

        $this->assertStringContainsString($expected, (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public function valueProvider(): array
    {
        return [
            'int' => [1, 'endAt=1'],
            'string' => ['value', 'endAt=%22value%22'],
        ];
    }
}
