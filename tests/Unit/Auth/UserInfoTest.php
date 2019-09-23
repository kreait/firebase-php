<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Kreait\Firebase\Auth\UserInfo;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class UserInfoTest extends UnitTestCase
{
    public function testJsonEncode()
    {
        $info = UserInfo::fromResponseData([
            'rawId' => 'some-uid',
            'providerId' => 'some-provider',
        ]);

        $decoded = \json_decode(\json_encode($info), false);

        $this->assertSame($info->uid, $decoded->uid);
        $this->assertSame($info->providerId, $decoded->providerId);
    }
}
