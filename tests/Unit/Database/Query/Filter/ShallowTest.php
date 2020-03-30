<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\Shallow;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class ShallowTest extends UnitTestCase
{
    public function testModifyUri(): void
    {
        $filter = new Shallow();

        $this->assertStringContainsString('shallow=true', (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }
}
