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
    /**
     * @var Reference|MockObject
     */
    private $reference;
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

    public function testGetReference(): void
    {
        self::assertSame($this->reference, $this->snapshotWithArrayValue->getReference());
    }

    public function testGetKey(): void
    {
        $this->reference->method('getKey')->willReturn('key');

        self::assertSame('key', $this->snapshotWithArrayValue->getKey());
    }

    public function testGetChildOnANonArrayValueReturnsAnEmptySnapshot(): void
    {
        $this->reference->expects(self::once())
            ->method('getChild')->with('path/to/child')
            ->willReturn($this->createMock(Reference::class));

        self::assertFalse($this->snapshotWithScalarValue->hasChild('path/to/child'));
        $childSnapshot = $this->snapshotWithScalarValue->getChild('path/to/child');

        self::assertNull($childSnapshot->getValue());
    }

    public function testGetChildOnANonExistingChildReturnsAnEmptySnapshot(): void
    {
        $this->reference->expects(self::once())
            ->method('getChild')->with('nonexisting/child')
            ->willReturn($this->createMock(Reference::class));

        self::assertFalse($this->snapshotWithArrayValue->hasChild('nonexisting/child'));
        self::assertNull($this->snapshotWithArrayValue->getChild('nonexisting/child')->getValue());
    }

    public function testGetChild(): void
    {
        $this->reference->expects(self::once())
            ->method('getChild')->with('key/subkey')
            ->willReturn($this->createMock(Reference::class));

        self::assertTrue($this->snapshotWithArrayValue->hasChild('key/subkey'));
        self::assertSame('value', $this->snapshotWithArrayValue->getChild('key/subkey')->getValue());
    }

    public function testExists(): void
    {
        self::assertTrue($this->snapshotWithArrayValue->exists());
        self::assertTrue($this->snapshotWithScalarValue->exists());
        self::assertFalse($this->snapshotWithEmptyValue->exists());
    }

    public function testHasChildren(): void
    {
        self::assertTrue($this->snapshotWithArrayValue->hasChildren());
        self::assertFalse($this->snapshotWithScalarValue->hasChildren());
        self::assertFalse($this->snapshotWithEmptyValue->hasChildren());
    }

    public function testNumChildren(): void
    {
        self::assertSame(1, $this->snapshotWithArrayValue->numChildren());
        self::assertSame(0, $this->snapshotWithScalarValue->numChildren());
        self::assertSame(0, $this->snapshotWithEmptyValue->numChildren());
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/212
     */
    public function testGetChildWithKeyStartingWithANonAlphabeticalCharacter(): void
    {
        $snapshot = new Snapshot($this->reference, [
            '123' => 'value',
            '-abc' => 'value',
        ]);

        self::assertTrue($snapshot->hasChild('123'));
        self::assertTrue($snapshot->hasChild('-abc'));
    }
}
