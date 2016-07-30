<?php

namespace Tests\Firebase\V2;

use Firebase\V2\Firebase;
use Firebase\V2\Http\Auth;
use GuzzleHttp\Psr7\Uri;
use Tests\FirebaseTestCase;

class FirebaseTest extends FirebaseTestCase
{
    /**
     * @var Uri
     */
    private $uri;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var Auth|\PHPUnit_Framework_MockObject_MockObject
     */
    private $auth;

    /**
     * @var Firebase
     */
    private $firebase;

    protected function setUp()
    {
        $this->uri = new Uri('https://any.firebaseio.com');
        $this->secret = 'secret';
        $this->auth = $this->createMock(Auth::class);

        $this->firebase = new Firebase($this->uri, $this->secret, $this->auth);
    }

    public function testCreateFromDatabaseUriAndSecret()
    {
        $uri = 'https://any.firebaseio.com';
        $secret = 'secret';

        $this->assertInstanceOf(Firebase::class, Firebase::fromDatabaseUriAndSecret($uri, $secret));
    }

    public function testDatabaseIsOnlyCreatedOnce()
    {
        $first = $this->firebase->getDatabase();
        $second = $this->firebase->getDatabase();

        $this->assertSame($first, $second);
    }

    public function testNewInstanceWhenAsUserWithClaims()
    {
        $this->assertNotSame($this->firebase, $this->firebase->asUserWithClaims('uid'));
    }
}
