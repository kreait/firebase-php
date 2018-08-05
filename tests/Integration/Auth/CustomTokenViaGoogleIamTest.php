<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Auth;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\CustomTokenViaGoogleIam;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Kreait\Firebase\Util\JSON;

class CustomTokenViaGoogleIamTest extends IntegrationTestCase
{
    /**
     * @var CustomTokenViaGoogleIam
     */
    private $generator;

    /**
     * @var Auth
     */
    private $auth;

    protected function setUp()
    {
        $this->generator = new CustomTokenViaGoogleIam(
            self::$serviceAccount->getClientEmail(),
            self::$factory->createApiClient()
        );

        $this->auth = self::$firebase->getAuth();
    }

    public function testCreateCustomToken()
    {
        $user = $this->auth->createUser([]);

        $idTokenResponse = $this->auth->getApiClient()->exchangeCustomTokenForIdAndRefreshToken(
            $this->generator->createCustomToken($user->uid)
        );
        $idToken = JSON::decode($idTokenResponse->getBody()->getContents(), true)['idToken'];

        $this->auth->verifyIdToken($idToken);

        $this->auth->deleteUser($user->uid);

        $this->assertTrue($noExceptionHasBeenThrown = true);
    }
}
