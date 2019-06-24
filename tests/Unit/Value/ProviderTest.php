<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Kreait\Firebase\Value\Provider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ProviderTest extends TestCase
{
    /**
     * @dataProvider validValues
     */
    public function testWithValidValue($value)
    {
        $provider = new Provider($value);

        $this->assertSame($value, (string) $provider);
        $this->assertSame($value, $provider->jsonSerialize());
        $this->assertTrue($provider->equalsTo($value));
    }

    public function validValues(): array
    {
        return [
            ['phone'],
        ];
    }
}
