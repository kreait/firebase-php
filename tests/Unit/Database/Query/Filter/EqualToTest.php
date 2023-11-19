<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\EqualTo;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class EqualToTest extends UnitTestCase
{
    #[DataProvider('valueProvider')]
    #[Test]
    public function modifyUri(mixed $given, string $expected): void
    {
        $filter = new EqualTo($given);

        $this->assertStringContainsString($expected, (string) $filter->modifyUri(new Uri('http://domain.example')));
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function valueProvider()
    {
        return [
            'int' => [1, 'equalTo=1'],
            'string' => ['value', 'equalTo=%22value%22'],
        ];
    }
}
