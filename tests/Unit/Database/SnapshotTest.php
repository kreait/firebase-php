<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database;

use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Snapshot;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
final class SnapshotTest extends UnitTestCase
{
    private Reference&MockObject $reference;
    private Snapshot $snapshotWithArrayValue;
    private Snapshot $snapshotWithScalarValue;
    private Snapshot $snapshotWithEmptyValue;

    protected function setUp(): void
    {
        $this->reference = $this->createMock(Reference::class);

        $this->snapshotWithArrayValue = new Snapshot($this->reference, ['key' => ['subkey' => 'value']]);
        $this->snapshotWithScalarValue = new Snapshot($this->reference, 'string');
        $this->snapshotWithEmptyValue = new Snapshot($this->reference, null);
    }

    /**
     * @test
     */
    public function getReference(): void
    {
        $this->assertSame($this->reference, $this->snapshotWithArrayValue->getReference());
    }

    /**
     * @test
     */
    public function getKey(): void
    {
        $this->reference->method('getKey')->willReturn('key');

        $this->assertSame('key', $this->snapshotWithArrayValue->getKey());
    }

    /**
     * @test
     */
    public function getChildOnANonArrayValueReturnsAnEmptySnapshot(): void
    {
        $this->reference->expects($this->once())
            ->method('getChild')->with('path/to/child')
            ->willReturn($this->createMock(Reference::class))
        ;

        $this->assertFalse($this->snapshotWithScalarValue->hasChild('path/to/child'));
        $childSnapshot = $this->snapshotWithScalarValue->getChild('path/to/child');

        $this->assertNull($childSnapshot->getValue());
    }

    /**
     * @test
     */
    public function getChildOnANonExistingChildReturnsAnEmptySnapshot(): void
    {
        $this->reference->expects($this->once())
            ->method('getChild')->with('nonexisting/child')
            ->willReturn($this->createMock(Reference::class))
        ;

        $this->assertFalse($this->snapshotWithArrayValue->hasChild('nonexisting/child'));
        $this->assertNull($this->snapshotWithArrayValue->getChild('nonexisting/child')->getValue());
    }

    /**
     * @test
     */
    public function getChild(): void
    {
        $this->reference->expects($this->once())
            ->method('getChild')->with('key/subkey')
            ->willReturn($this->createMock(Reference::class))
        ;

        $this->assertTrue($this->snapshotWithArrayValue->hasChild('key/subkey'));
        $this->assertSame('value', $this->snapshotWithArrayValue->getChild('key/subkey')->getValue());
    }

    /**
     * @test
     */
    public function exists(): void
    {
        $this->assertTrue($this->snapshotWithArrayValue->exists());
        $this->assertTrue($this->snapshotWithScalarValue->exists());
        $this->assertFalse($this->snapshotWithEmptyValue->exists());
    }

    /**
     * @test
     */
    public function hasChildren(): void
    {
        $this->assertTrue($this->snapshotWithArrayValue->hasChildren());
        $this->assertFalse($this->snapshotWithScalarValue->hasChildren());
        $this->assertFalse($this->snapshotWithEmptyValue->hasChildren());
    }

    /**
     * @test
     */
    public function numChildren(): void
    {
        $this->assertSame(1, $this->snapshotWithArrayValue->numChildren());
        $this->assertSame(0, $this->snapshotWithScalarValue->numChildren());
        $this->assertSame(0, $this->snapshotWithEmptyValue->numChildren());
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/212
     *
     * @test
     */
    public function getChildWithKeyStartingWithANonAlphabeticalCharacter(): void
    {
        $snapshot = new Snapshot($this->reference, [
            '123' => 'value',
            '-abc' => 'value',
        ]);

        $this->assertTrue($snapshot->hasChild('123'));
        $this->assertTrue($snapshot->hasChild('-abc'));
    }
}
