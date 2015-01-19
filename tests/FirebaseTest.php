<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreait\Firebase;

use Ivory\HttpAdapter\Configuration;
use Ivory\HttpAdapter\FopenHttpAdapter;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Ivory\HttpAdapter\Message\Response;
use Ivory\HttpAdapter\Message\Stream\StringStream;

class FirebaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Firebase
     */
    protected $instance;

    /**
     * @var string
     */
    protected $baseUrl = 'https://brilliant-torch-1474.firebaseio.com';

    protected function setUp()
    {
        parent::setUp();

        $this->instance = new Firebase($this->baseUrl);
    }

    public function testDefaultState()
    {
        $this->assertAttributeInstanceOf('Ivory\HttpAdapter\CurlHttpAdapter', 'http', $this->instance);
        $this->assertInstanceOf('Psr\Log\NullLogger', $this->instance->getLogger());
        $this->assertEquals($this->baseUrl, $this->instance->getBaseUrl());
    }

    public function testInitializeFirebaseWithCustomHttpAdapter()
    {
        $f = new Firebase($this->baseUrl, $http = new FopenHttpAdapter());

        $this->assertAttributeSame($http, 'http', $f);
    }

    public function testSetLogger()
    {
        $this->instance->setLogger($logger = $this->getMock('Psr\Log\LoggerInterface'));
        $this->assertAttributeSame($logger, 'logger', $this->instance);
        $this->assertSame($logger, $this->instance->getLogger());
    }

    public function testSet()
    {
        $data = [
            'key' => 'value2',
            'key2' => 'value2'
        ];

        $http = $this->getMockHttpAdapter();
        $http->method('sendRequest')->willReturn($this->getJsonResponse($data));

        $f = new Firebase($this->baseUrl, $http);

        $result = $f->set($data, 'test/' . __METHOD__);

        $this->assertEquals($data, $result);
    }

    public function testPush()
    {
        $data = [
            'key' => 'value2',
            'key2' => 'value2'
        ];

        $http = $this->getMockHttpAdapter();
        $http->method('sendRequest')->willReturn($this->getJsonResponse(['name' => 'foo']));

        $f = new Firebase($this->baseUrl, $http);

        $result = $f->push($data, 'test/' . __METHOD__);

        $this->assertEquals('foo', $result);
    }

    public function testGet()
    {
        $data = [
            'key' => 'value2',
            'key2' => 'value2'
        ];

        $http = $this->getMockHttpAdapter();
        $http->method('sendRequest')->willReturn($this->getJsonResponse($data));

        $f = new Firebase($this->baseUrl, $http);

        $result = $f->get('test/' . __METHOD__);

        $this->assertEquals($data, $result);
    }

    public function testGetWithShallowOption()
    {
        $data = [
            'key' => 'value2',
            'key2' => 'value2'
        ];

        $http = $this->getMockHttpAdapter();
        $http->method('sendRequest')->willReturn($this->getJsonResponse($data));

        $f = new Firebase($this->baseUrl, $http);

        $result = $f->get('test/' . __METHOD__, ['shallow' => true]);

        $this->assertEquals($data, $result);
    }

    /**
     * @return HttpAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockHttpAdapter()
    {
        $http = $this->getMockBuilder('Ivory\HttpAdapter\HttpAdapterInterface')
            ->getMock();

        $http->method('getConfiguration')
            ->willReturn(new Configuration());

        return $http;
    }

    protected function getJsonResponse(array $data)
    {
        return new Response(200, 'OK', Response::PROTOCOL_VERSION_1_1, [], new StringStream(json_encode($data)));
    }
}
