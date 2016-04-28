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

class ReferenceTest extends IntegrationTest
{
    /**
     * @var Reference
     */
    protected $reference;

    protected function setUp()
    {
        parent::setUp();

        $this->reference = new Reference($this->firebase, $this->getLocation());
    }

    public function testGetKey()
    {
        $locationPath = explode('/', $this->getLocation());
        $expected = array_pop($locationPath);

        $this->assertEquals($expected, $this->reference->getKey());
    }

    public function testGetLocation()
    {
        $this->assertSame($this->getLocation(), $this->reference->getLocation());
    }

    public function testGetReference()
    {
        $expectedFullLocation = $this->getLocation().'/bar';

        $reference = $this->reference->getReference('bar');

        $this->assertInstanceOf(ReferenceInterface::class, $reference);
        $this->assertEquals($expectedFullLocation, $reference->getLocation());
    }

    public function testGetMagicReference()
    {
        $reference = $this->reference->foo;

        $expectedFullLocation = $this->getLocation().'/foo';

        $this->assertInstanceOf(ReferenceInterface::class, $reference);
        $this->assertEquals($expectedFullLocation, $reference->getLocation());
    }

    public function testSet()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $data = ['key1' => 'value1', 'key2' => 'value2', 'key3' => null];
        $expectedData = ['key1' => 'value1', 'key2' => 'value2'];

        $reference = $this->reference->set($data);

        $this->assertInstanceOf('Kreait\Firebase\Reference', $reference);
        $this->assertAttributeEquals($expectedData, 'data', $reference);
    }

    public function testPush()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $reference = $this->reference->getReference(__FUNCTION__);

        $initialData = ['key1' => 'value1', 'key2' => 'value2'];
        $newItem = ['key3' => 'value3', 'key4' => null];
        $expectedNewItemData = ['key3' => 'value3'];

        $expectedFullData = ['key1' => 'value1', 'key2' => 'value2'];

        $reference->set($initialData);

        $newItemReference = $reference->push($newItem);
        $newItemData = $newItemReference->getData();
        $this->assertEquals($expectedNewItemData, $newItemData);

        $expectedFullData[$newItemReference->getKey()] = $newItemReference->getData();

        $this->assertEquals($expectedFullData, $reference->getData());
    }

    public function testUpdate()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $reference = $this->reference->getReference(__FUNCTION__);

        $initialData = ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'];
        $update = ['key2' => 'new_value', 'key3' => null];
        $expectedData = ['key1' => 'value1', 'key2' => 'new_value'];

        $reference->set($initialData);
        $reference->update($update);

        $this->assertEquals($expectedData, $reference->getData());
    }

    public function testUpdateWithMultiDimensonalData()
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
        $reference = $this->reference->getReference(__FUNCTION__);

        $reference->set($initialData);
        $reference->update($update);

        $this->assertEquals($expectedResult, $reference->getData());
    }

    public function testDelete()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $reference = $this->reference->getReference(__FUNCTION__);

        $initialData = ['sub1' => 'value1', 'sub2' => 'value2'];
        $reference->set($initialData);

        $subReference = $reference->getReference('sub1');
        $subReference->delete();
    }

    public function testGetExistingData()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $reference = $this->reference->getReference(__FUNCTION__);

        $firstData = ['first' => 'value'];
        $secondData = ['second' => 'value'];

        $reference->set($firstData);
        $this->assertEquals($firstData, $reference->getData());

        $reference->set($secondData);
        $this->assertEquals($secondData, $reference->getData());
    }

    public function testArrayAccessOffsetGetAndOffsetExists()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $reference = $this->reference->getReference(__FUNCTION__);

        $initialData = ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'];

        $reference->set($initialData);

        $this->assertTrue($reference->offsetExists('key1'));
        $this->assertFalse($reference->offsetExists('nonexistent'));
        $this->assertEquals('value1', $reference->offsetGet('key1'));
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     */
    public function testOffsetGetWithUndefinedIndex()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $reference = $this->reference;

        $reference->offsetGet('nonexistent');
    }

    public function testOffsetSet()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $reference = $this->reference->getReference(__FUNCTION__);

        $reference->offsetSet('key', 'value');
        $this->assertTrue($reference->offsetExists('key'));
    }

    public function testOffsetUnset()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $reference = $this->reference->getReference(__FUNCTION__);

        $reference->set(['key' => 'value']);

        $reference->offsetUnset('key');
        $this->assertAttributeNotContains('value', 'data', $reference);
    }

    public function testCount()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $reference = $this->reference->getReference(__FUNCTION__);

        $initialData = ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'];

        $reference->set($initialData);

        $this->assertEquals(3, count($reference));
    }

    public function testQuery()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();
        $reference = $this->reference->getReference(__FUNCTION__);

        $query = new Query();
        $query
            ->orderByKey()
            ->limitToFirst(1);

        $referenceResult = $reference->query($query);
        $firebaseResult = $this->firebase->query($this->getLocation(__FUNCTION__), $query);

        $this->assertEquals($referenceResult, $firebaseResult);
    }
}
