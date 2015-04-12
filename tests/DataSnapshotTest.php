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

class DataSnapshotTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $referenceProphecy;

    /**
     * @var ReferenceInterface
     */
    private $reference;

    /**
     * @var DataSnapshot
     */
    private $emptySnapshot;

    /**
     * @var DataSnapshot
     */
    private $singleValueSnapshot;

    /**
     * @var DataSnapshot
     */
    private $flatArraySnapshot;

    protected function setUp()
    {
        $this->referenceProphecy = TestHelpers::createMockReference('foo');
        $this->reference = $this->referenceProphecy->reveal();

        $this->emptySnapshot = new DataSnapshot($this->reference);
        $this->singleValueSnapshot = new DataSnapshot($this->reference, 'foo');
        $this->flatArraySnapshot = new DataSnapshot($this->reference, [
            'key1' => 'val1',
            'key2' => 'val2',
        ]);
    }

    public function testSnapshotInitializedWithObjectGetsTransformedToArray()
    {
        $data = ['a' => 'b', 'c' => 'd'];
        $dataObject = (object) $data;

        $snapshot = new DataSnapshot($this->reference, $dataObject);

        $this->assertAttributeInternalType('array', 'data', $snapshot);
    }

    public function testGetKey()
    {
        $this->assertEquals('foo', $this->emptySnapshot->key());
    }

    public function testGetName()
    {
        $this->assertEquals('foo', $this->emptySnapshot->name());
    }

    public function testGetReference()
    {
        $this->assertSame($this->reference, $this->emptySnapshot->getReference());
    }

    public function testEmptySnapshotHasNoChildren()
    {
        $this->assertFalse($this->emptySnapshot->hasChildren());
    }

    public function testEmptySnapshotHasZeroChildren()
    {
        $this->assertEquals(0, $this->emptySnapshot->numChildren());
    }

    public function testEmptySnaphostDoesNotExist()
    {
        $this->assertFalse($this->emptySnapshot->exists());
    }

    public function testEmptyShanpshotHasNullValue()
    {
        $this->assertNull($this->emptySnapshot->val());
    }

    public function testEmptySnapshotReturnsNullForAnyChild()
    {
        $this->assertNull($this->emptySnapshot->child('any/path'));
    }

    public function testSingleValueHasNoChildren()
    {
        $this->assertFalse($this->singleValueSnapshot->hasChildren());
    }

    public function testSingleValueSnapshotHasZeroChildren()
    {
        $this->assertEquals(0, $this->singleValueSnapshot->numChildren());
    }

    public function testSingleSnapshotReturnsNullForAnyChild()
    {
        $this->assertNull($this->emptySnapshot->child('any/path'));
    }

    public function testSingleValueSnapshotHasUnchangedValue()
    {
        $this->assertSame('foo', $this->singleValueSnapshot->val());
    }

    public function testFlatArraySnapshotHasChildren()
    {
        $this->assertTrue($this->flatArraySnapshot->hasChildren());
    }

    public function testFlatArraySnapshotHasTwoChildren()
    {
        $this->assertEquals(2, $this->flatArraySnapshot->numChildren());
    }

    public function testFlatArraySnapshotFindsChild()
    {
        $child1 = $this->flatArraySnapshot->child('key1');
        $child2 = $this->flatArraySnapshot->child('key2');

        $this->assertInstanceOf(get_class($this->flatArraySnapshot), $child1);
        $this->assertInstanceOf(get_class($this->flatArraySnapshot), $child2);

        $this->assertEquals('val1', $child1->val());
        $this->assertEquals('val2', $child2->val());
    }

    public function testFlatArraySnapshotReturnsNullForUnknownChild()
    {
        $this->assertNull($this->flatArraySnapshot->child('any/path'));
    }

    /**
     * @param array $data
     * @dataProvider arrayDataProvider
     */
    public function testHasChild($data)
    {
        $snapshot = $this->createDataSnapshot($data);

        $this->assertTrue($snapshot->hasChild('a'));
        $this->assertTrue($snapshot->hasChild('a/b'));
        $this->assertTrue($snapshot->hasChild('a/b/c'));

        $this->assertFalse($snapshot->hasChild('b'));
        $this->assertFalse($snapshot->hasChild('a/b/c/d'));
    }

    /**
     * @param array $data
     * @dataProvider arrayDataProvider
     */
    public function testGetChild($data)
    {
        $reference = TestHelpers::createMockReference('a', 'b/c/d');
        $snapshot = new DataSnapshot($reference->reveal(), ['b' => ['c' => 'd']]);

        $child = $snapshot->child('b');

        $this->assertInstanceOf(get_class($snapshot), $child);
    }

    /**
     * @param mixed $data
     * @dataProvider stringDataProvider
     */
    public function testHasChildShouldReturnFalseWhenDataIsNotAnArray($data)
    {
        $snapshot = $this->createDataSnapshot($data);

        $this->assertFalse($snapshot->hasChild('any/path'));
    }

    /**
     * @param mixed $data
     *
     * @return DataSnapshot
     */
    private function createDataSnapshot($data)
    {
        return new DataSnapshot($this->referenceProphecy->reveal(), $data);
    }

    /**
     * @param string $location
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    private function createMockReference($location)
    {
        return TestHelpers::createMockReference($location);
    }

    public function arrayDataProvider()
    {
        return [
            [
                [
                    'a' => [
                        'b' => [
                            'c' => 'd',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function stringDataProvider()
    {
        return [
            ['string'],
        ];
    }
}
