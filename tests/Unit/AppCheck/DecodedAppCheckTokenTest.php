<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\AppCheck;

use Kreait\Firebase\AppCheck\DecodedAppCheckToken;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class DecodedAppCheckTokenTest extends UnitTestCase
{
    public function testCreateFromValidArray(): void
    {
        $options = DecodedAppCheckToken::fromArray([
            'app_id' => $appId = '1:111111111111:android:0000000000000000000000',
            'aud' => $aud = ['111111111111', 'project-id'],
            'exp' => $exp = '1667915200',
            'iat' => $iat = '1667915500',
            'iss' => $iss = 'https://firebaseappcheck.googleapis.com/111111111111',
            'sub' => $appId,
        ]);

        $this->assertSame($appId, $options->app_id());
        $this->assertSame($aud, $options->aud());
        $this->assertSame($exp, $options->exp());
        $this->assertSame($iat, $options->iat());
        $this->assertSame($iss, $options->iss());
    }
}
