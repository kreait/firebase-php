<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */
namespace Kreait\Firebase;

class ReferenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FirebaseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $firebase;

    /**
     * @var string
     */
    protected $location = 'path/to/location';

    protected function setUp()
    {
        $this->firebase = $this->getMockBuilder('Kreait\Firebase\FirebaseInterface')->getMock();
    }

    public function testGetKey()
    {
        $locationPath = explode('/', $this->location);
        $expected = array_pop($locationPath);

        $this->assertEquals($expected, $this->getReference()->getKey());
    }

    public function testGetReference()
    {
        $expectedFullLocation = $this->location.'/bar';

        $this->firebase
            ->expects($this->once())
            ->method('getReference')
            ->with($expectedFullLocation)
            ->willReturn(new Reference($this->firebase, $expectedFullLocation))
        ;

        $check = $this->getReference()->getReference('bar');
        $this->assertAttributeEquals($expectedFullLocation, 'location', $check);
    }

    public function testSet()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2', 'key3' => null];
        $expectedData = ['key1' => 'value1', 'key2' => 'value2'];

        $this->firebase
            ->expects($this->once())
            ->method('set')
            ->with($data, $this->location)
            ->willReturn($expectedData)
        ;

        $reference = $this->getReference();

        $result = $reference->set($data);

        $this->assertSame($reference, $result);
        $this->assertAttributeEquals($expectedData, 'data', $result);
    }

    public function testPush()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2', 'key3' => null];
        $expectedData = ['key1' => 'value1', 'key2' => 'value2'];
        $expectedKey = 'foo';
        $childLocation = $this->location.'/'.$expectedKey;

        $this->firebase
            ->expects($this->once())
            ->method('push')
            ->with($data, $this->location)
            ->willReturn($expectedKey)
        ;

        $this->firebase
            ->expects($this->once())
            ->method('getReference')
            ->with($childLocation)
            ->willReturn($this->getReference($expectedData, $childLocation));

        $reference = $this->getReference();

        $result = $reference->push($data);

        $this->assertInstanceOf('Kreait\Firebase\Reference', $result);
        $this->assertNotSame($reference, $result);
        $this->assertEquals($expectedKey, $result->getKey());
    }

    public function testUpdate()
    {
        $existingData = ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'];
        $update = ['key2' => 'new_value', 'key3' => null];
        $expectedData = ['key1' => 'value1', 'key2' => 'new_value', 'key3' => 'value3'];

        $this->firebase
            ->expects($this->once())
            ->method('update')
            ->with($update, $this->location)
            ->willReturn($expectedData)
        ;

        $reference = $this->getReference($existingData);

        $result = $reference->update($update);

        $this->assertSame($reference, $result);
        $this->assertAttributeEquals($expectedData, 'data', $result);
    }

    public function testDelete()
    {
        $this->firebase
            ->expects($this->once())
            ->method('delete')
            ->with($this->location)
            ->willReturn(null)
        ;

        $reference = $this->getReference();
        $reference->delete();
    }

    public function testGetExistingData()
    {
        $existingData = ['key1' => 'value1', 'key2' => 'new_value', 'key3' => 'value3'];
        $reference = $this->getReference($existingData);

        $this->firebase
            ->expects($this->never())
            ->method('get')
        ;

        $this->assertEquals($existingData, $reference->getData());
    }

    public function testGetDataWhichThatHasToBeFetchedFirst()
    {
        $data = ['key1' => 'value1', 'key2' => 'new_value', 'key3' => 'value3'];

        $this->firebase
            ->expects($this->once())
            ->method('get')
            ->with($this->location)
            ->willReturn($data)
        ;

        $this->assertEquals($data, $this->getReference()->getData());
    }

    public function testArrayAccessOffsetGetAndOffsetExists()
    {
        $reference = $this->getReference(['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3']);

        $this->assertTrue($reference->offsetExists('key1'));
        $this->assertFalse($reference->offsetExists('nonexistent'));
        $this->assertEquals('value1', $reference->offsetGet('key1'));
    }

    public function testOffsetGetWithUndefinedIndex()
    {
        $reference = $this->getReference();
        $this->assertNull($reference->offsetGet('nonexistent'));
    }

    public function testOffsetSet()
    {
        $this->firebase
            ->expects($this->once())
            ->method('update')
            ->willReturn(['key' => 'value'])
        ;

        $reference = $this->getReference();
        $reference->offsetSet('key', 'value');
        $this->assertTrue($reference->offsetExists('key'));
    }

    public function testOffsetUnset()
    {
        $reference = $this->getReference(['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3']);

        $reference->offsetUnset('key1');
        $this->assertAttributeNotContains('value1', 'data', $reference);
    }

    public function testCount()
    {
        $reference = $this->getReference(['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3']);

        $this->assertEquals(3, count($reference));
    }

    /**
     * @param  array       $predefinedData
     * @param  string|null $location
     * @return Reference
     */
    protected function getReference(array $predefinedData = [], $location = null)
    {
        $location = $location ?: $this->location;
        $reference = new Reference($this->firebase, $location);

        if (empty($predefinedData)) {
            return $reference;
        }

        $r = new \ReflectionObject($reference);
        $attrData = $r->getProperty('data');
        $attrData->setAccessible(true);
        $attrData->setValue($reference, $predefinedData);
        $attrData->setAccessible(false);

        return $reference;
    }
}
