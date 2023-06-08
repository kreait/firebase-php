<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\Shallow;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class ShallowTest extends UnitTestCase
{
    #[Test]
    public function modifyUri(): void
    {
        $filter = new Shallow();

        $this->assertStringContainsString('shallow=true', (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }
}
