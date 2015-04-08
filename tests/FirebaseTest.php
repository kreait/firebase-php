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

namespace Kreait\Firebase;

use Ivory\HttpAdapter\HttpAdapterInterface;
use Ivory\HttpAdapter\Message\Response;

class FirebaseTest extends IntegrationTest
{
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

        $this->assertSame($data['string'], $this->firebase->get($location.'/string'));
        $this->assertSame($data['int'], $this->firebase->get($location.'/int'));
        $this->assertSame($data['float'], $this->firebase->get($location.'/float'));
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
}
