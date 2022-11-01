<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Auth;

use Google\Auth\CredentialsLoader;
use Google\Auth\SignBlobInterface;
use Kreait\Firebase\Auth\CustomTokenViaGoogleCredentials;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;

/**
 * @internal
 */
final class CustomTokenViaGoogleCredentialsTest extends IntegrationTestCase
{
    private string $uid = 'some-uid';
    private CustomTokenViaGoogleCredentials $generator;
    private Auth $auth;

    protected function setUp(): void
    {
        $credentials = CredentialsLoader::makeCredentials(Factory::API_CLIENT_SCOPES, self::$serviceAccount->asArray());

        if (!$credentials instanceof SignBlobInterface) {
            $this->markTestSkipped('No credential capable of signing custom tokens found');
        }

        $this->generator = new CustomTokenViaGoogleCredentials($credentials);
        $this->auth = self::$factory->createAuth();
    }

    public function testCreateCustomToken(): void
    {
        $token = $this->generator->createCustomToken($this->uid, ['a-claim' => 'a-value']);

        $check = $this->auth->signInWithCustomToken($token);

        $this->assertSame($this->uid, $check->firebaseUserId());
    }

    public function testAGeneratedCustomTokenCanBeParsed(): void
    {
        $token = $this->generator->createCustomToken($this->uid, ['a-claim' => 'a-value']);

        $tokenString = trim($token->toString(), '=');
        $parsed = (new Parser(new JoseEncoder()))->parse($tokenString);

        $this->assertInstanceOf(UnencryptedToken::class, $parsed);
        $this->assertSame($this->uid, $parsed->claims()->get('uid'));
    }
}
