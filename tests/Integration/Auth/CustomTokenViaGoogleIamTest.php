<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Auth;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\CustomTokenViaGoogleIam;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Kreait\Firebase\Util\JSON;

/**
 * @internal
 */
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
            $this->generator->createCustomToken($user->uid, ['a-claim' => 'a-value'])
        );
        $idToken = JSON::decode($idTokenResponse->getBody()->getContents(), true)['idToken'];

        $verifiedToken = $this->auth->verifyIdToken($idToken);

        $this->assertTrue($verifiedToken->hasClaim('a-claim'));
        $this->assertSame('a-value', $verifiedToken->getClaim('a-claim'));

        $this->assertTrue($verifiedToken->hasClaim('user_id'));
        $this->assertSame($user->uid, $verifiedToken->getClaim('user_id'));

        $this->auth->deleteUser($user->uid);
    }
}
