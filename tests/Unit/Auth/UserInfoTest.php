<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Beste\Json;
use Kreait\Firebase\Auth\UserInfo;
use Kreait\Firebase\Tests\UnitTestCase;

use function json_decode;

/**
 * @internal
 */
final class UserInfoTest extends UnitTestCase
{
    public function testJsonEncode(): void
    {
        $info = UserInfo::fromResponseData([
            'rawId' => 'some-uid',
            'providerId' => 'some-provider',
        ]);

        $decoded = json_decode(Json::encode($info), false);

        $this->assertSame($info->uid, $decoded->uid);
        $this->assertSame($info->providerId, $decoded->providerId);
    }
}
