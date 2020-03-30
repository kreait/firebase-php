<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\EqualTo;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class EqualToTest extends UnitTestCase
{
    public function testCreateWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new EqualTo(new \stdClass());
    }

    /**
     * @dataProvider valueProvider
     */
    public function testModifyUri($given, $expected): void
    {
        $filter = new EqualTo($given);

        $this->assertStringContainsString($expected, (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }

    public function valueProvider()
    {
        return [
            [1, 'equalTo=1'],
            ['value', 'equalTo=%22value%22'],
        ];
    }
}
