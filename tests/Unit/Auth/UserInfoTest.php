<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Kreait\Firebase\Auth\UserInfo;
use Kreait\Firebase\Tests\UnitTestCase;
use Kreait\Firebase\Util\JSON;

/**
 * @internal
 */
class UserInfoTest extends UnitTestCase
{
    public function testJsonEncode(): void
    {
        $info = UserInfo::fromResponseData([
            'rawId' => 'some-uid',
            'providerId' => 'some-provider',
        ]);

        $decoded = \json_decode(JSON::encode($info), false);

        $this->assertSame($info->uid, $decoded->uid);
        $this->assertSame($info->providerId, $decoded->providerId);
    }
}
