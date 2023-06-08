<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Beste\Json;
use Kreait\Firebase\Messaging\RegistrationToken;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RegistrationTokenTest extends TestCase
{
    /**
     * @dataProvider valueProvider
     *
     * @test
     */
    public function fromValue(string $expected, string $value): void
    {
        $token = RegistrationToken::fromValue($value);

        $this->assertSame($expected, $token->value());
        $this->assertSame('"'.$token.'"', Json::encode($token));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function valueProvider(): array
    {
        return [
            'foo' => ['foo', 'foo'],
        ];
    }
}
