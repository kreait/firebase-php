<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Request;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Request\UpdateUser;
use Kreait\Firebase\Util\JSON;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class UpdateUserTest extends TestCase
{
    /**
     * @dataProvider propertiesProvider
     */
    public function testWithProperties(array $properties, array $expected)
    {
        $request = UpdateUser::withProperties($properties);

        $this->assertEquals($expected, \json_decode(\json_encode($request), true));
    }

    public function testWithMissingUid()
    {
        $this->expectException(InvalidArgumentException::class);
        UpdateUser::withProperties([])->jsonSerialize();
    }

    public function propertiesProvider(): array
    {
        // All non-mentioned attributes are already tested through the CreateUserTest
        $given = ['uid' => 'some-uid'];
        $expected = ['localId' => 'some-uid'];

        return [
            [
                $given + ['deletephoto' => true],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            [
                $given + ['deletephotourl' => true],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            [
                $given + ['removephoto' => true],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            [
                $given + ['removephotourl' => true],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            [
                $given + ['deleteAttribute' => 'photo'],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            [
                $given + ['deleteAttribute' => 'photourl'],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            [
                $given + ['deleteAttributes' => ['photo']],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            [
                $given + ['deleteAttributes' => ['photourl']],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            [
                $given + ['deleteDisplayName' => true],
                $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
            ],
            [
                $given + ['removeDisplayName' => true],
                $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
            ],
            [
                $given + ['deleteDisplayName' => true],
                $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
            ],
            [
                $given + ['deletephone' => true],
                $expected + ['deleteProvider' => ['phone']],
            ],
            [
                $given + ['deletephonenumber' => true],
                $expected + ['deleteProvider' => ['phone']],
            ],
            [
                $given + ['removephone' => true],
                $expected + ['deleteProvider' => ['phone']],
            ],
            [
                $given + ['removephonenumber' => true],
                $expected + ['deleteProvider' => ['phone']],
            ],
            [
                $given + ['phone' => null],
                $expected + ['deleteProvider' => ['phone']],
            ],
            [
                $given + ['phonenumber' => null],
                $expected + ['deleteProvider' => ['phone']],
            ],
            [
                $given + ['deleteprovider' => 'phone'],
                $expected + ['deleteProvider' => ['phone']],
            ],
            [
                $given + ['deleteproviders' => ['phone', 'password']],
                $expected + ['deleteProvider' => ['phone', 'password']],
            ],
            [
                $given + ['removeprovider' => 'phone'],
                $expected + ['deleteProvider' => ['phone']],
            ],
            [
                $given + ['removeproviders' => ['phone', 'password']],
                $expected + ['deleteProvider' => ['phone', 'password']],
            ],
            [
                $given + ['deleteAttribute' => 'displayname'],
                $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
            ],
            [
                $given + ['deleteAttributes' => ['displayname']],
                $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
            ],
            [
                $given + ['emailVerified' => true],
                $expected + ['emailVerified' => true],
            ],
            [
                $given + ['emailVerified' => false],
                $expected + ['emailVerified' => false],
            ],
            [
                $given + ['emailVerified' => null],
                $expected,
            ],
            [
                $given + ['customClaims' => $claims = ['admin' => true, 'groupId' => '1234']],
                $expected + ['customAttributes' => JSON::encode($claims)],
            ],
            [
                $given + ['customAttributes' => $claims = ['admin' => true, 'groupId' => '1234']],
                $expected + ['customAttributes' => JSON::encode($claims)],
            ],
        ];
    }
}
