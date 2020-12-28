<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Auth;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @internal
 */
class DeleteUsersTest extends IntegrationTestCase
{
    /** @var Auth */
    private $auth;

    protected function setUp(): void
    {
        $this->auth = self::$factory->createAuth();
    }

    public function testDeleteUsers(): void
    {
        $this->auth->createUser(
            CreateUser::new()
                ->withUid($uidFirst = \bin2hex(\random_bytes(5)))
        );

        // Enabled users can only be force deleted
        $result = $this->auth->deleteUsers([$uidFirst], ['force' => 'true']);

        $this->assertEquals(1, $result->getSuccessCount());
    }
}
