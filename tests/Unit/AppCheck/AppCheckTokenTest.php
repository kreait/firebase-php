<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\AppCheck;

use Kreait\Firebase\AppCheck\AppCheckToken;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class AppCheckTokenTest extends UnitTestCase
{
    #[Test]
    public function createFromValidArray(): void
    {
        $options = AppCheckToken::fromArray([
            'ttl' => $ttl = '300',
            'token' => $token = 'jwtToken',
        ]);

        $this->assertSame($ttl, $options->ttl);
        $this->assertSame($token, $options->token);
    }
}
