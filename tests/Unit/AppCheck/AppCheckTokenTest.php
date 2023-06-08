<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\AppCheck;

use Kreait\Firebase\AppCheck\AppCheckToken;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class AppCheckTokenTest extends UnitTestCase
{
    /**
     * @test
     */
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
