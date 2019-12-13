<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth\ActionCodeSettings;

use Kreait\Firebase\Auth\ActionCodeSettings\Raw;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RawTest extends TestCase
{
    /** @test */
    public function it_returns_what_it_receives()
    {
        $data = ['foo' => 'bar'];

        $this->assertSame($data, (new Raw($data))->toArray());
    }
}
