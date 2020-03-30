<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth\ActionCodeSettings;

use Kreait\Firebase\Auth\ActionCodeSettings\RawActionCodeSettings;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RawActionSettingsTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_what_it_receives(): void
    {
        $data = ['foo' => 'bar'];

        $this->assertSame($data, (new RawActionCodeSettings($data))->toArray());
    }
}
