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
use Ivory\HttpAdapter\Event\Subscriber\TapeRecorderSubscriber;
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
    protected $baseUrl;

    /**
     * @var string
     */
    protected $baseLocation;

    /**
     * @var TapeRecorderSubscriber
     */
    protected $recorder;

    protected function setUp()
    {
        parent::setUp();

        $this->baseUrl = getenv('FIREBASE_HOST');
        $this->baseLocation = getenv('FIREBASE_BASE_LOCATION') ?: 'tests';
        $recordingMode = getenv('FIREBASE_TEST_RECORDING_MODE') ?: 1;

        $this->http = new CurlHttpAdapter();
        $this->firebase = new Firebase($this->baseUrl, $this->http);
        $this->recorder = new TapeRecorderSubscriber(__DIR__.'/fixtures/FirebaseTest');
        $this->recorder->setRecordingMode($recordingMode);

        $this->http->getConfiguration()->getEventDispatcher()->addSubscriber($this->recorder);
    }

    protected function tearDown()
    {
        $this->recorder->eject();
    }

    /**
     * @expectedException \Kreait\Firebase\Exception\PermissionDeniedException
     */
    public function testUnauthenticatedCallToForbiddenLocationThrowsPermissionDeniedException()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $this->firebase->get('forbidden');
    }

    public function testUnauthenticatedCallToAllowedLocationDoesNotThrowException()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $this->firebase->get($this->getLocation());
    }

    /**
     * @expectedException \Kreait\Firebase\Exception\FirebaseException
     */
    public function testHttpCallToBogusDomainThrowsHttpAdapterException()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $f = new Firebase('https://'.uniqid());

        $f->get($this->getLocation());
    }

    /**
     * @expectedException \Kreait\Firebase\Exception\FirebaseException
     */
    public function testServerReturns400PlusAndThrowsFirebaseException()
    {
        $http = $this->getHttpAdapter();
        $http
            ->expects($this->once())
            ->method('sendRequest')->willReturn($response = $this->getInternalServerErrorResponse());

        $f = new Firebase($this->baseUrl, $http);
        $f->get($this->getLocation());
    }

    public function testGet()
    {
        $data = ['key1' => 'value1', 'key2' => null];
        $expectedData = ['key1' => 'value1'];

        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $this->firebase->set($data, $this->getLocation(__FUNCTION__));
        $result = $this->firebase->get($this->getLocation(__FUNCTION__));

        $this->assertEquals($expectedData, $result);
    }

    public function testGetScalar()
    {
        $data = [
            'string' => 'string',
            'int' => 1,
            'float' => 1.1,
        ];

        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $location = $this->getLocation(__FUNCTION__);
        $this->firebase->set($data, $location);

        $this->assertSame($data['string'], $this->firebase->get($location . '/string'));
        $this->assertSame($data['int'], $this->firebase->get($location . '/int'));
        $this->assertSame($data['float'], $this->firebase->get($location . '/float'));
    }

    public function testGetKeyWithWhitespace()
    {
        // This should not throw an exception
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $this->firebase->get($this->getLocation(__FUNCTION__.'/My Key'));
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

        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $result = $this->firebase->set($data, $this->getLocation(__FUNCTION__));
        $this->assertEquals($expectedResult, $result);
    }

    public function testUpdate()
    {
        $initialData = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => [
                'subkey1' => 'subvalue1',
                'subkey2' => 'subvalue2',
            ],
        ];

        $update = [
            'key1' => 'value1',
            'key2' => null,
            'key4' => [
                'subkey1' => 'subvalue1',
                'subkey2' => 'subvalue2',
            ],
        ];

        $expectedResult = [
            'key1' => 'value1',
            'key4' => [
                'subkey1' => 'subvalue1',
                'subkey2' => 'subvalue2',
            ],
        ];

        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $this->firebase->set($initialData, $this->getLocation(__FUNCTION__));
        $result = $this->firebase->update($update, $this->getLocation(__FUNCTION__));
        $this->assertEquals($expectedResult, $result);
    }

    public function testDeletingANonExistentLocationDoesNotThrowAnException()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $this->firebase->delete($this->getLocation(__FUNCTION__));
    }

    public function testDelete()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->firebase->set(['key' => 'value'], $this->getLocation(__FUNCTION__));
        $this->firebase->delete($this->getLocation(__FUNCTION__));
        $result = $this->firebase->get($this->getLocation(__FUNCTION__));

        $this->assertEmpty($result);
    }

    public function testPush()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $data = ['key' => 'value'];

        $key = $this->firebase->push($data, $this->getLocation(__FUNCTION__));

        $this->assertStringStartsWith('-', $key);
    }

    public function testGetOnNonExistentLocation()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $result = $this->firebase->get($this->getLocation('non_existing'));

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

    protected function getLocation($subLocation = null)
    {
        if (!$subLocation) {
            return $this->baseLocation;
        }

        return $this->baseLocation.'/'.$subLocation;
    }
}
