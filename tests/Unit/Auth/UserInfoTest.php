<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Beste\Json;
use Kreait\Firebase\Auth\UserInfo;
use Kreait\Firebase\Tests\UnitTestCase;

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

        $decoded = Json::decode(Json::encode($info), false);

        self::assertSame($info->uid, $decoded->uid);
        self::assertSame($info->providerId, $decoded->providerId);
    }
}
