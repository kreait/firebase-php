<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Request;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Request\UpdateUser;
use PHPUnit\Framework\TestCase;

class UpdateUserTest extends TestCase
{
    /**
     * @dataProvider propertiesProvider
     */
    public function testWithProperties(array $properties, array $expected)
    {
        $request = UpdateUser::withProperties($properties);

        $this->assertEquals($expected, json_decode(json_encode($request), true));
    }

    public function testWithUid()
    {
        $request = UpdateUser::new('some-uid');

        $this->assertTrue($request->hasUid());
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
                $given + ['deleteAttribute' => 'displayname'],
                $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
            ],
            [
                $given + ['deleteAttributes' => ['displayname']],
                $expected + ['deleteAttribute' => [UpdateUser::DISPLAY_NAME]],
            ],
        ];
    }
}
