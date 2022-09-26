<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\LimitToFirst;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class LimitToFirstTest extends UnitTestCase
{
    public function testCreateWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LimitToFirst(0);
    }

    public function testModifyUri(): void
    {
        $filter = new LimitToFirst(3);

        $this->assertStringContainsString('limitToFirst=3', (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }
}
