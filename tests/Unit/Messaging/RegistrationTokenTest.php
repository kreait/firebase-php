<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Messaging\RegistrationToken;
use Kreait\Firebase\Util\JSON;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RegistrationTokenTest extends TestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function testFromValue(string $expected, string $value): void
    {
        $token = RegistrationToken::fromValue($value);

        $this->assertSame($expected, $token->value());
        $this->assertSame('"'.$token.'"', JSON::encode($token));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function valueProvider(): array
    {
        return [
            'foo' => ['foo', 'foo'],
        ];
    }
}
