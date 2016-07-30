<?php

namespace Tests\Firebase\Database;

use Firebase\Database\Reference;
use Firebase\Database\Snapshot;
use Tests\FirebaseTestCase;

class SnapshotTest extends FirebaseTestCase
{
    /**
     * @var Reference|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reference;

    /**
     * @var Snapshot
     */
    private $snapshotWithArrayValue;

    /**
     * @var Snapshot
     */
    private $snapshotWithScalarValue;

    /**
     * @var Snapshot
     */
    private $snapshotWithEmptyValue;

    protected function setUp()
    {
        $this->reference = $this->createMock(Reference::class);

        $this->snapshotWithArrayValue = new Snapshot($this->reference, ['key' => ['subkey' => 'value']]);
        $this->snapshotWithScalarValue = new Snapshot($this->reference, 'string');
        $this->snapshotWithEmptyValue = new Snapshot($this->reference, null);
    }

    public function testGetReference()
    {
        $this->assertSame($this->reference, $this->snapshotWithArrayValue->getReference());
    }

    public function testGetKey()
    {
        $this->reference->expects($this->any())->method('getKey')->willReturn('key');

        $this->assertSame('key', $this->snapshotWithArrayValue->getKey());
    }

    public function testGetChildOnANonArrayValueReturnsAnEmptySnapshot()
    {
        $this->reference->expects($this->once())
            ->method('getChild')->with('path/to/child')
            ->willReturn($this->createMock(Reference::class));

        $this->assertFalse($this->snapshotWithScalarValue->hasChild('path/to/child'));
        $childSnapshot = $this->snapshotWithScalarValue->getChild('path/to/child');

        $this->assertNull($childSnapshot->getValue());
    }

    public function testGetChildOnANonExistingChildReturnsAnEmptySnapshot()
    {
        $this->reference->expects($this->once())
            ->method('getChild')->with('nonexisting/child')
            ->willReturn($this->createMock(Reference::class));

        $this->assertFalse($this->snapshotWithArrayValue->hasChild('nonexisting/child'));
        $this->assertNull($this->snapshotWithArrayValue->getChild('nonexisting/child')->getValue());
    }

    public function testGetChild()
    {
        $this->reference->expects($this->once())
            ->method('getChild')->with('key/subkey')
            ->willReturn($this->createMock(Reference::class));

        $this->assertTrue($this->snapshotWithArrayValue->hasChild('key/subkey'));
        $this->assertSame('value', $this->snapshotWithArrayValue->getChild('key/subkey')->getValue());
    }

    public function testExists()
    {
        $this->assertTrue($this->snapshotWithArrayValue->exists());
        $this->assertTrue($this->snapshotWithScalarValue->exists());
        $this->assertFalse($this->snapshotWithEmptyValue->exists());
    }

    public function testHasChildren()
    {
        $this->assertTrue($this->snapshotWithArrayValue->hasChildren());
        $this->assertFalse($this->snapshotWithScalarValue->hasChildren());
        $this->assertFalse($this->snapshotWithEmptyValue->hasChildren());
    }

    public function testNumChildren()
    {
        $this->assertSame(1, $this->snapshotWithArrayValue->numChildren());
        $this->assertSame(0, $this->snapshotWithScalarValue->numChildren());
        $this->assertSame(0, $this->snapshotWithEmptyValue->numChildren());
    }
}
