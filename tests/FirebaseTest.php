<?php

namespace Kreait\Tests;

use Firebase\Auth\Token\Handler;
use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Database;
use Kreait\Firebase\ServiceAccount;

class FirebaseTest extends FirebaseTestCase
{
    /**
     * @var ServiceAccount|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceAccount;

    /**
     * @var Uri
     */
    private $databaseUri;

    /**
     * @var Firebase
     */
    private $firebase;

    protected function setUp()
    {
        $this->serviceAccount = $this->createServiceAccountMock();
        $this->databaseUri = new Uri('https://database-uri.tld');

        $this->firebase = new Firebase($this->serviceAccount, $this->databaseUri);
    }

    public function testWithDatabaseUri()
    {
        $firebase = $this->firebase->withDatabaseUri('https://some-other-uri.tld');

        $this->assertInstanceOf(Firebase::class, $firebase);
        $this->assertNotSame($this->firebase, $firebase);
    }

    public function testGetDatabase()
    {
        $db = $this->firebase->getDatabase();

        $this->assertInstanceOf(Database::class, $db);
    }

    public function testAsUserWithClaimsWithUid()
    {
        $firebase = $this->firebase->asUserWithClaims('uid');
        $this->assertInstanceOf(Firebase::class, $firebase);
        $this->assertNotSame($this->firebase, $firebase);
    }

    public function testAsUserWithClaimsWithUser()
    {
        $user = $this->prophesize(Auth\User::class);
        $user->getUid()->willReturn('uid');

        $firebase = $this->firebase->asUser($user->reveal());
        $this->assertInstanceOf(Firebase::class, $firebase);
        $this->assertNotSame($this->firebase, $firebase);
    }

    public function testGetTokenHandler()
    {
        $firebase = new Firebase($this->serviceAccount, $this->databaseUri);
        $this->assertInstanceOf(Handler::class, $firebase->getTokenHandler());
    }

    public function testGetAuth()
    {
        $this->assertInstanceOf(Auth::class, $this->firebase->getAuth());
    }
}
