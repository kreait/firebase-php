<?php

/*
 * This file is part of the firebase-php package.
 *
 * (c) Jérôme Gamez <jerome@kreait.com>
 * (c) kreait GmbH <info@kreait.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Kreait\Firebase\Auth;

use Firebase\JWT\JWT;

class TokenGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenGenerator
     */
    protected $generator;

    protected function setUp()
    {
        $this->generator = new TokenGenerator('foo');
    }

    public function testSecretIsSet()
    {
        $this->assertEquals('foo', $this->generator->getSecret());
    }

    public function testDebugFlagIsDisabledByDefault()
    {
        $this->assertDebugFlagIsFalse($this->generator->createCustomToken('foo'));
    }

    public function testEnableDebugOnlyWorksForTheNextGeneratedToken()
    {
        $generator = new TokenGenerator('foo', false);

        $token = $generator
            ->enableDebug()
            ->createCustomToken('foo');

        $this->assertDebugFlagIsTrue($token);

        // Check that the next token has the previous setting again
        $this->assertDebugFlagIsFalse($generator->createCustomToken('foo'));
    }

    public function testDisableDebugOnlyWorksForTheNextGeneratedToken()
    {
        $generator = new TokenGenerator('foo', true);

        $token = $generator
            ->disableDebug()
            ->createCustomToken('foo');

        $this->assertDebugFlagIsFalse($token);

        // Check that the next token has the previous setting again
        $this->assertDebugFlagIsTrue($generator->createCustomToken('foo'));
    }

    public function testCreateAnonymousToken()
    {
        $token = $this->generator->createAnonymousToken();

        $this->assertProvider('anonymous', $token);
    }

    public function testTwoAnonymousTokensAreDifferent()
    {
        $first = $this->generator->createAnonymousToken();
        $second = $this->generator->createAnonymousToken();

        $firstData = $this->decodeTokenToArray($first);
        $secondData = $this->decodeTokenToArray($second);

        $this->assertNotEquals($first, $second);
        $this->assertNotSame($firstData['d']['uid'], $secondData['d']['uid']);
    }

    public function testAdminToken()
    {
        $token = $this->generator->createAdminToken();
        $data = $this->decodeTokenToArray($token);

        $this->assertTrue($data['admin']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidCredentialsWillThrowRuntimeException()
    {
        $invalid = str_pad('', 1024, 'x');
        $this->generator->createCustomToken($invalid);
    }

    public function testCreateCustomToken()
    {
        $token = $this->generator->createCustomToken('uid', ['foo' => 'bar']);
        $data = $this->decodeTokenToArray($token);

        $this->assertEquals('uid', $data['d']['uid']);
        $this->assertEquals('bar', $data['d']['foo']);
    }

    protected function decodeTokenToArray($token)
    {
        $obj = JWT::decode($token, 'foo', ['HS256']);

        return json_decode(json_encode($obj), true);
    }

    protected function assertDebugFlagIsTrue($token)
    {
        $data = $this->decodeTokenToArray($token);
        $this->assertTrue($data['debug']);
    }

    protected function assertDebugFlagIsFalse($token)
    {
        $data = $this->decodeTokenToArray($token);
        $this->assertFalse($data['debug']);
    }

    protected function assertProvider($provider, $token)
    {
        $data = $this->decodeTokenToArray($token);

        $this->assertSame($provider, $data['d']['provider']);
        $this->assertContains($provider, $data['d']['uid']);
    }

    protected function assertNotSameProvider($provider, $token)
    {
        $data = $this->decodeTokenToArray($token);

        $this->assertNotSame($provider, $data['d']['provider']);
        $this->assertNotContains($provider, $data['d']['uid']);
    }
}
