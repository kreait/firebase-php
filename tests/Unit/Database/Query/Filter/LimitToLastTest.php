<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\LimitToLast;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class LimitToLastTest extends UnitTestCase
{
    /**
     * @test
     */
    public function createWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LimitToLast(0);
    }

    /**
     * @test
     */
    public function modifyUri(): void
    {
        $filter = new LimitToLast(3);

        $this->assertStringContainsString('limitToLast=3', (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }
}
