<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreait\Firebase;

use Ivory\HttpAdapter\Configuration;
use Ivory\HttpAdapter\CurlHttpAdapter;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Ivory\HttpAdapter\Message\Response;

class FirebaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Firebase
     */
    protected $firebase;

    /**
     * @var HttpAdapterInterface
     */
    protected $http;

    /**
     * @var string
     */
    protected $baseUrl = 'https://brilliant-torch-1474.firebaseio.com';

    /**
     * @var string
     */
    protected $baseLocation;

    protected function setUp()
    {
        parent::setUp();

        // $this->http = $this->getHttpAdapter();
        $this->http = new CurlHttpAdapter();
        $this->firebase = new Firebase($this->baseUrl, $this->http);
        $this->baseLocation = 'test';
    }

    protected function tearDown()
    {
        $this->firebase->delete($this->baseLocation);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage array or object expected, string given
     */
    public function testInvalidArgumentThrowsException()
    {
        $this->firebase->set('string_is_invalid', 'test/'.__FUNCTION__);
    }

    /**
     * @expectedException \Kreait\Firebase\FirebaseException
     */
    public function testHttpCallThrowsHttpAdapterException()
    {
        $f = new Firebase('https://' . uniqid());

        $f->get($this->baseLocation . '/' . $this->baseLocation);
    }

    /**
     * @expectedException \Kreait\Firebase\FirebaseException
     */
    public function testServerReturns400PlusAndThrowsFirebaseException()
    {
        $http = $this->getHttpAdapter();
        $http
            ->expects($this->once())
            ->method('sendRequest')->willReturn($response = $this->getInternalServerErrorResponse());

        $f = new Firebase($this->baseUrl, $http);
        $f->get($this->baseLocation);
    }

    public function testGet()
    {
        $data = ['key1' => 'value1', 'key2' => null];
        $expectedData = ['key1' => 'value1'];

        $this->firebase->set($data, $this->baseLocation . '/' . __FUNCTION__);
        $result = $this->firebase->get($this->baseLocation . '/' . __FUNCTION__);

        $this->assertEquals($expectedData, $result);
    }

    public function testSet()
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => null,
        ];

        $expectedResult = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $result = $this->firebase->set($data, $this->baseLocation . '/' . __FUNCTION__);
        $this->assertEquals($expectedResult, $result);
    }

    public function testUpdate()
    {
        $update = [
            'key1' => 'value1',
            'key2' => null,
        ];

        $expectedResult = [
            'key1' => 'value1',
        ];

        $result = $this->firebase->update($update, $this->baseLocation . '/' . __FUNCTION__);
        $this->assertEquals($expectedResult, $result);
    }

    public function testDeletingANonExistentLocationDoesNotThrowAnException()
    {
        $this->firebase->delete($this->baseLocation . '/' . __FUNCTION__);
    }

    public function testDelete()
    {
        $this->firebase->set(['key' => 'value'], $this->baseLocation . '/' . __FUNCTION__);

        $this->firebase->delete($this->baseLocation . '/' . __FUNCTION__);

        $result = $this->firebase->get($this->baseLocation . '/' . __FUNCTION__);

        $this->assertEmpty($result);
    }

    public function testPush()
    {
        $data = ['key' => 'value'];

        $location = $this->baseLocation.'/'.__FUNCTION__;

        $key = $this->firebase->push($data, $location);

        $this->assertStringStartsWith('-', $key);
    }

    public function testGetOnNonExistentLocation()
    {
        $result = $this->firebase->get(uniqid());

        $this->assertEmpty($result);
    }

    /**
     * @return HttpAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getHttpAdapter()
    {
        $http = $this->getMockBuilder('Ivory\HttpAdapter\HttpAdapterInterface')
            ->getMock();

        $http
            ->expects($this->any())
            ->method('getConfiguration')
            ->willReturn(new Configuration());

        return $http;
    }

    protected function getInternalServerErrorResponse()
    {
        return new Response(500, 'Internal Server Error', Response::PROTOCOL_VERSION_1_1);
    }
}
