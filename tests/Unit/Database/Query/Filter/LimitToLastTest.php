<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\LimitToLast;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class LimitToLastTest extends UnitTestCase
{
    #[Test]
    public function createWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LimitToLast(0);
    }

    #[Test]
    public function modifyUri(): void
    {
        $filter = new LimitToLast(3);

        $this->assertStringContainsString('limitToLast=3', (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }
}
