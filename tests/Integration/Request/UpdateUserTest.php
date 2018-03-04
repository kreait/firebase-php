<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Request;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Request\UpdateUser;
use Kreait\Firebase\Tests\IntegrationTestCase;

class UpdateUserTest extends IntegrationTestCase
{
    /**
     * @var Auth
     */
    private $auth;

    protected function setUp()
    {
        $this->auth = self::$firebase->getAuth();
    }

    public function testRemovePhotoUrl()
    {
        $photoUrl = 'http://example.com/a_photo.jpg';

        $user = $this->auth->createUser(CreateUser::new()->withPhotoUrl($photoUrl));
        $this->assertSame($user->photoUrl, $photoUrl);

        $updatedUser = $this->auth->updateUser(UpdateUser::new($user->uid)->withRemovedPhotoUrl());

        $this->assertNull($updatedUser->photoUrl);

        $this->auth->deleteUser($user->uid);
    }

    public function testRemoveDisplayName()
    {
        $displayName = 'A display name';

        $user = $this->auth->createUser(CreateUser::new()->withDisplayName($displayName));
        $this->assertSame($user->displayName, $displayName);

        $updatedUser = $this->auth->updateUser(UpdateUser::new($user->uid)->withRemovedDisplayName());

        $this->assertNull($updatedUser->displayName);

        $this->auth->deleteUser($user->uid);
    }
}
