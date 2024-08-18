<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Request;

use Beste\Json;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Request\UpdateUser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class UpdateUserTest extends TestCase
{
    /**
     * @param array<array<string|mixed>> $properties
     * @param array<array<string|mixed>> $expected
     */
    #[DataProvider('propertiesProvider')]
    #[Test]
    public function withProperties(array $properties, array $expected): void
    {
        $request = UpdateUser::withProperties($properties);

        $this->assertEqualsCanonicalizing($expected, $request->jsonSerialize());
    }

    #[Test]
    public function withMissingUid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        UpdateUser::withProperties([])->jsonSerialize();
    }

    public static function propertiesProvider(): \Iterator
    {
        // All non-mentioned attributes are already tested through the CreateUserTest
        $given = ['uid' => 'some-uid'];
        $expected = ['localId' => 'some-uid'];
        yield 'deletephoto' => [
            $given + ['deletephoto' => true],
            $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
        ];
        yield 'deletephotourl' => [
            $given + ['deletephotourl' => true],
            $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
        ];
        yield 'removephoto' => [
            $given + ['removephoto' => true],
            $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
        ];
        yield 'removephotourl' => [
            $given + ['removephotourl' => true],
            $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
        ];
        yield 'deleteAttribute photo' => [
            $given + ['deleteAttribute' => 'photo'],
            $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
        ];
        yield 'deleteAttribute photourl' => [
            $given + ['deleteAttribute' => 'photourl'],
            $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
        ];
        yield 'deleteAttributes photo' => [
            $given + ['deleteAttributes' => ['photo']],
            $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
        ];
        yield 'deleteAttributes photourl' => [
            $given + ['deleteAttributes' => ['photourl']],
            $expected + ['deleteAttribute' => [UpdateUser::PHOTO_URL]],
        ];
        yield 'deleteDisplayName' => [
            $given + ['deleteDisplayName' => true],
            $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
        ];
        yield 'removeDisplayName' => [
            $given + ['removeDisplayName' => true],
            $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
        ];
        yield 'deleteAttribute email' => [
            $given + ['deleteAttribute' => 'email'],
            $expected + ['deleteAttribute' => [UpdateUser::EMAIL]],
        ];
        yield 'deleteEmail' => [
            $given + ['deleteEmail' => true],
            $expected + ['deleteAttribute' => [UpdateUser::EMAIL]],
        ];
        yield 'removeEmail' => [
            $given + ['removeEmail' => true],
            $expected + ['deleteAttribute' => [UpdateUser::EMAIL]],
        ];
        yield 'deletephone' => [
            $given + ['deletephone' => true],
            $expected + ['deleteProvider' => ['phone']],
        ];
        yield 'deletephonenumber' => [
            $given + ['deletephonenumber' => true],
            $expected + ['deleteProvider' => ['phone']],
        ];
        yield 'removephone' => [
            $given + ['removephone' => true],
            $expected + ['deleteProvider' => ['phone']],
        ];
        yield 'removephonenumber' => [
            $given + ['removephonenumber' => true],
            $expected + ['deleteProvider' => ['phone']],
        ];
        yield 'phone is null' => [
            $given + ['phone' => null],
            $expected + ['deleteProvider' => ['phone']],
        ];
        yield 'phonenumber is null' => [
            $given + ['phonenumber' => null],
            $expected + ['deleteProvider' => ['phone']],
        ];
        yield 'deleteprovider phone' => [
            $given + ['deleteprovider' => 'phone'],
            $expected + ['deleteProvider' => ['phone']],
        ];
        yield 'deleteproviders phone, password' => [
            $given + ['deleteproviders' => ['phone', 'password']],
            $expected + ['deleteProvider' => ['phone', 'password']],
        ];
        yield 'removeprovider phone' => [
            $given + ['removeprovider' => 'phone'],
            $expected + ['deleteProvider' => ['phone']],
        ];
        yield 'removeproviders phone, password' => [
            $given + ['removeproviders' => ['phone', 'password']],
            $expected + ['deleteProvider' => ['phone', 'password']],
        ];
        yield 'deleteAttribute displayname' => [
            $given + ['deleteAttribute' => 'displayname'],
            $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
        ];
        yield 'deleteAttributes displayname' => [
            $given + ['deleteAttributes' => ['displayname']],
            $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
        ];
        yield 'emailVerified true' => [
            $given + ['emailVerified' => true],
            $expected + ['emailVerified' => true],
        ];
        yield 'emailVerified false' => [
            $given + ['emailVerified' => false],
            $expected + ['emailVerified' => false],
        ];
        yield 'emailVerified null' => [
            $given + ['emailVerified' => null],
            $expected,
        ];
        yield 'customClaims' => [
            $given + ['customClaims' => $claims = ['admin' => true, 'groupId' => '1234']],
            $expected + ['customAttributes' => Json::encode($claims)],
        ];
        yield 'customAttributes' => [
            $given + ['customAttributes' => $claims = ['admin' => true, 'groupId' => '1234']],
            $expected + ['customAttributes' => Json::encode($claims)],
        ];
    }
}
