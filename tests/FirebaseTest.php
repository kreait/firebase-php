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
use Ivory\HttpAdapter\HttpAdapterException;
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage array or object expected, string given
     */
    public function testInvalidArgumentThrowsException()
    {
        $http = $this->getMockHttpAdapter();
        $f = new Firebase($this->baseUrl, $http);

        $f->set('string_is_invalid', 'test/' . __METHOD__);
    }

    public function testHttpCallThrowsHttpAdapterException()
    {
        $http = $this->getMockHttpAdapter();
        $f = new Firebase($this->baseUrl, $http);
        $http
            ->expects($this->once())
            ->method('sendRequest')
            ->willThrowException($e = new HttpAdapterException('Some exception'));

        $data = ['does' => 'not matter'];

        $expectedExceptionMessage = $e->getMessage();

        $this->setExpectedException('\Kreait\Firebase\FirebaseException', $expectedExceptionMessage);

        $f->set($data, 'test/' . __METHOD__);
    }

    public function testInternalServerErrorWithoutBodyThrowsFirebaseException()
    {
        $http = $this->getMockHttpAdapter();
        $http
            ->expects($this->once())
            ->method('sendRequest')->willReturn($response = $this->getInternalServerErrorResponse());

        $f = new Firebase($this->baseUrl, $http);
        $data = ['any' => 'data'];

        $expectedExceptionMessage = sprintf(
            'Server error (%s) for URL %s.json with data "%s"',
            $response->getStatusCode(),
            $this->baseUrl . '/test/' . __METHOD__,
            json_encode($data)
        );

        $this->setExpectedException('Kreait\Firebase\FirebaseException', $expectedExceptionMessage);

        $f->set($data, 'test/' . __METHOD__);
    }

    public function testBadRequestThrowsFirebaseException()
    {
        $http = $this->getMockHttpAdapter();
        $http
            ->expects($this->once())
            ->method('sendRequest')->willReturn($response = $this->getBadRequestErrorResponse());

        $f = new Firebase($this->baseUrl, $http);
        $data = ['any' => 'data'];

        $expectedExceptionMessage = sprintf(
            'Server error (%s) for URL %s.json with data "%s"',
            $response->getStatusCode(),
            $this->baseUrl . '/test/' . __METHOD__,
            json_encode($data)
        );

        $this->setExpectedException('Kreait\Firebase\FirebaseException', $expectedExceptionMessage);

        $f->set($data, 'test/' . __METHOD__);
    }

    public function testFirebaseException()
    {
        $http = $this->getMockHttpAdapter();
        $http
            ->expects($this->once())
            ->method('sendRequest')->willReturn($response = $this->getBadRequestErrorResponse());

        $f = new Firebase($this->baseUrl, $http);
        $data = ['any' => 'data'];

        try {
            $f->set($data, 'test/' . __METHOD__);
            $this->fail('An exception should have been thrown');
        } catch (FirebaseException $e) {
            $this->assertTrue($e->hasRequest());
            $this->assertTrue($e->hasResponse());
            $this->assertInstanceOf('\Ivory\HttpAdapter\Message\RequestInterface', $e->getRequest());
            $this->assertInstanceOf('\Ivory\HttpAdapter\Message\ResponseInterface', $e->getResponse());
        } catch (\Exception $e) {
            $this->fail('No other exception than a FirebaseException should be thrown');
        }
    }

    public function testSet()
    {
        $data = [
            'key' => 'value2',
            'key2' => 'value2'
        ];

        $http = $this->getMockHttpAdapter();
        $http
            ->expects($this->once())
            ->method('sendRequest')->willReturn($this->getJsonResponse($data));

        $f = new Firebase($this->baseUrl, $http);

        $result = $f->set($data, 'test/' . __METHOD__);

        $this->assertEquals($data, $result);
    }

    public function testSetWithEmptyValue()
    {
        $data = [
            'key' => 'value2',
            'empty' => null
        ];

        $expected = [
            'key' => 'value2',
        ];

        $http = $this->getMockHttpAdapter();
        $http
            ->expects($this->once())
            ->method('sendRequest')->willReturn($this->getJsonResponse($expected));

        $f = new Firebase($this->baseUrl, $http);

        $result = $f->set($data, 'test/' . __METHOD__);

        $this->assertEquals($expected, $result);
    }

    public function testUpdate()
    {
        $data = [
            'key' => 'value',
            'empty' => null,
        ];

        $http = $this->getMockHttpAdapter();
        $http
            ->expects($this->once())
            ->method('sendRequest')->willReturn($this->getJsonResponse($data));

        $f = new Firebase($this->baseUrl, $http);

        $result = $f->update($data, 'test/' . __METHOD__);

        $this->assertEquals($data, $result);
    }

    public function testDelete()
    {
        $http = $this->getMockHttpAdapter();
        $http
            ->expects($this->once())
            ->method('sendRequest')->willReturn($this->getDeleteOkResponse());

        $f = new Firebase($this->baseUrl, $http);

        $f->delete('test/' . __METHOD__);
        // No assertion needed, the important thing is that no exception is thrown
    }

    public function testPush()
    {
        $data = [
            'key' => 'value2',
            'key2' => 'value2'
        ];

        $http = $this->getMockHttpAdapter();
        $http
            ->expects($this->once())
            ->method('sendRequest')->willReturn($this->getJsonResponse(['name' => 'foo']));

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
        $http
            ->expects($this->once())
            ->method('sendRequest')->willReturn($this->getJsonResponse($data));

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
        $http
            ->expects($this->once())
            ->method('sendRequest')->willReturn($this->getJsonResponse($data));

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

        $http
            ->expects($this->any())
            ->method('getConfiguration')
            ->willReturn(new Configuration());

        return $http;
    }

    protected function getJsonResponse(array $data)
    {
        return new Response(200, 'OK', Response::PROTOCOL_VERSION_1_1, [], new StringStream(json_encode($data)));
    }

    protected function getDeleteOkResponse()
    {
        return new Response(204, 'OK', Response::PROTOCOL_VERSION_1_1);
    }

    protected function getInternalServerErrorResponse()
    {
        return new Response(500, 'Internal Server Error', Response::PROTOCOL_VERSION_1_1);
    }

    protected function getBadRequestErrorResponse()
    {
        $data = [
            'error' => 'Client error'
        ];

        return new Response(400, 'Bad Request', Response::PROTOCOL_VERSION_1_1, [], new StringStream(json_encode($data)));
    }
}
