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

class FirebaseTest extends IntegrationTest
{
    public function testGetDefaultConfiguration()
    {
        $this->assertSame($this->configuration, $this->firebase->getConfiguration());
    }

    public function testSetConfiguration()
    {
        $prophecy = $this->prophesize('Kreait\Firebase\ConfigurationInterface');
        /** @var ConfigurationInterface $configuration */
        $configuration = $prophecy->reveal();
        $this->firebase->setConfiguration($configuration);

        $this->assertSame($configuration, $this->firebase->getConfiguration());
    }

    /**
     * @expectedException \Kreait\Firebase\Exception\PermissionDeniedException
     */
    public function testUnauthenticatedCallToForbiddenLocationThrowsPermissionDeniedException()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->firebase->removeAuthOverride();
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

    public function testGetReference()
    {
        $reference = $this->firebase->getReference('foo');
        $this->assertInstanceOf(ReferenceInterface::class, $reference);
        $this->assertEquals('foo', $reference->getLocation());
    }

    public function testGetMagicReference()
    {
        $reference = $this->firebase->foo;
        $this->assertInstanceOf(ReferenceInterface::class, $reference);
        $this->assertEquals('foo', $reference->getLocation());
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

    public function testGetWithServiceAccountAuthentication()
    {
        $this->configuration->setAuthConfigFile(__DIR__.'/google-service-account.json');

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
        $this->firebase->set($data, $this->getLocation(__FUNCTION__));

        $check = $this->firebase->get($this->getLocation(__FUNCTION__));
        $this->assertEquals($expectedResult, $check);
    }

    /**
     * @expectedException \Kreait\Firebase\Exception\FirebaseException
     */
    public function testSetWithInvalidData()
    {
        $this->firebase->set('string', $this->getLocation(__FUNCTION__));
    }

    public function testUpdate()
    {
        $initialData = [
            'key' => 'value',
        ];

        $update = [
            'key' => [
                'string' => 'string',
                'int' => 1,
                'float' => 1.1,
                'bool_false' => false,
                'bool_true' => true,
                'null' => null,
                'subkey' => [
                    'string' => 'string',
                    'int' => 1,
                    'float' => 1.1,
                    'bool_false' => false,
                    'bool_true' => true,
                    'null' => null,
                ]
            ]
        ];

        $expectedResult = [
            'key' => [
                'string' => 'string',
                'int' => 1,
                'float' => 1.1,
                'bool_false' => false,
                'bool_true' => true,
                'subkey' => [
                    'string' => 'string',
                    'int' => 1,
                    'float' => 1.1,
                    'bool_false' => false,
                    'bool_true' => true,
                ]
            ]
        ];

        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $this->firebase->set($initialData, $this->getLocation(__FUNCTION__));
        $this->firebase->update($update, $this->getLocation(__FUNCTION__));

        $check = $this->firebase->get($this->getLocation(__FUNCTION__));

        $this->assertEquals($expectedResult, $check);
    }

    /**
     * @expectedException \Kreait\Firebase\Exception\FirebaseException
     */
    public function testUpdateWithInvalidData()
    {
        $this->firebase->update('string', $this->getLocation(__FUNCTION__));
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

    /**
     * @expectedException \Kreait\Firebase\Exception\FirebaseException
     */
    public function testPushWithInvalidData()
    {
        $this->firebase->push('string', $this->getLocation(__FUNCTION__));
    }

    public function testGetOnNonExistentLocation()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $result = $this->firebase->get($this->getLocation('non_existing'));

        $this->assertEmpty($result);
    }
}
