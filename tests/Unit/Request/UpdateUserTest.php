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
     *
     * @param array<array<string|mixed>> $properties
     * @param array<array<string|mixed>> $expected
     */
    public function testWithProperties(array $properties, array $expected): void
    {
        $request = UpdateUser::withProperties($properties);

        $this->assertEquals($expected, $request->jsonSerialize());
    }

    public function testWithMissingUid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        UpdateUser::withProperties([])->jsonSerialize();
    }

    /**
     * @return array<string, array<array<string|mixed>>>
     */
    public function propertiesProvider(): array
    {
        // All non-mentioned attributes are already tested through the CreateUserTest
        $given = ['uid' => 'some-uid'];
        $expected = ['localId' => 'some-uid'];

        return [
            'deletephoto' => [
                $given + ['deletephoto' => true],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            'deletephotourl' => [
                $given + ['deletephotourl' => true],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            'removephoto' => [
                $given + ['removephoto' => true],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            'removephotourl' => [
                $given + ['removephotourl' => true],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            'deleteAttribute photo' => [
                $given + ['deleteAttribute' => 'photo'],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            'deleteAttribute photourl' => [
                $given + ['deleteAttribute' => 'photourl'],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            'deleteAttributes photo' => [
                $given + ['deleteAttributes' => ['photo']],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            'deleteAttributes photourl' => [
                $given + ['deleteAttributes' => ['photourl']],
                $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
            ],
            'deleteDisplayName' => [
                $given + ['deleteDisplayName' => true],
                $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
            ],
            'removeDisplayName' => [
                $given + ['removeDisplayName' => true],
                $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
            ],
            'deleteAttribute email' => [
                $given + ['deleteAttribute' => 'email'],
                $expected + ['deleteAttribute' => [UpdateUser::EMAIL]],
            ],
            'deleteEmail' => [
                $given + ['deleteEmail' => true],
                $expected + ['deleteAttribute' => [UpdateUser::EMAIL]],
            ],
            'removeEmail' => [
                $given + ['removeEmail' => true],
                $expected + ['deleteAttribute' => [UpdateUser::EMAIL]],
            ],
            'deletephone' => [
                $given + ['deletephone' => true],
                $expected + ['deleteProvider' => ['phone']],
            ],
            'deletephonenumber' => [
                $given + ['deletephonenumber' => true],
                $expected + ['deleteProvider' => ['phone']],
            ],
            'removephone' => [
                $given + ['removephone' => true],
                $expected + ['deleteProvider' => ['phone']],
            ],
            'removephonenumber' => [
                $given + ['removephonenumber' => true],
                $expected + ['deleteProvider' => ['phone']],
            ],
            'phone is null' => [
                $given + ['phone' => null],
                $expected + ['deleteProvider' => ['phone']],
            ],
            'phonenumber is null' => [
                $given + ['phonenumber' => null],
                $expected + ['deleteProvider' => ['phone']],
            ],
            'deleteprovider phone' => [
                $given + ['deleteprovider' => 'phone'],
                $expected + ['deleteProvider' => ['phone']],
            ],
            'deleteproviders phone, password' => [
                $given + ['deleteproviders' => ['phone', 'password']],
                $expected + ['deleteProvider' => ['phone', 'password']],
            ],
            'removeprovider phone' => [
                $given + ['removeprovider' => 'phone'],
                $expected + ['deleteProvider' => ['phone']],
            ],
            'removeproviders phone, password' => [
                $given + ['removeproviders' => ['phone', 'password']],
                $expected + ['deleteProvider' => ['phone', 'password']],
            ],
            'deleteAttribute displayname' => [
                $given + ['deleteAttribute' => 'displayname'],
                $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
            ],
            'deleteAttributes displayname' => [
                $given + ['deleteAttributes' => ['displayname']],
                $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
            ],
            'emailVerified true' => [
                $given + ['emailVerified' => true],
                $expected + ['emailVerified' => true],
            ],
            'emailVerified false' => [
                $given + ['emailVerified' => false],
                $expected + ['emailVerified' => false],
            ],
            'emailVerified null' => [
                $given + ['emailVerified' => null],
                $expected,
            ],
            'customClaims' => [
                $given + ['customClaims' => $claims = ['admin' => true, 'groupId' => '1234']],
                $expected + ['customAttributes' => JSON::encode($claims)],
            ],
            'customAttributes' => [
                $given + ['customAttributes' => $claims = ['admin' => true, 'groupId' => '1234']],
                $expected + ['customAttributes' => JSON::encode($claims)],
            ],
        ];
    }
}
