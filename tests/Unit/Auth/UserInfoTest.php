<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Kreait\Firebase\Auth\UserInfo;
use Kreait\Firebase\Tests\UnitTestCase;
use Kreait\Firebase\Util\JSON;
use PHPUnit\Framework\TestCase;

class UserInfoTest extends UnitTestCase
{
    public function testJsonEncode()
    {
        $info = UserInfo::fromResponseData([
            'rawId' => 'some-uid',
            'providerId' => 'some-provider',
        ]);

        $expected = ['uid' => 'some-uid', 'providerId' => 'some-provider'];
        $this->assertEquals($expected, JSON::decode(JSON::encode($info), true));
    }
}
