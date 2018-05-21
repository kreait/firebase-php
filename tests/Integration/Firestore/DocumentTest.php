<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Firestore;

use Kreait\Firebase\Firestore\Reference;
use Kreait\Firebase\Tests\Integration\FirestoreTestCase;

class DocumentTest extends FirestoreTestCase
{
    /**
     * @var Collection
     */
    private $collection;

    public function setUp()
    {
        $this->collection = self::$db->getCollection(self::$testCollection);
    }

    /**
     * @param $key
     * @param $value
     *
     * @dataDisabledProvider validValues
     */
    public function testSetAndGet()
    {
        $doc = $this->collection->getDocument(__FUNCTION__);

        $doc->set($this->validValues());

        $snap = $doc->getSnapshot();

        foreach ($this->validValues() as $key => $value) {
            $this->assertSame($value, $snap[$key]);
        }
    }

    public function testUpdate()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');

        $ref = $this->ref->getChild(__FUNCTION__);
        $ref->set([
            'first' => 'value',
            'second' => 'value',
        ]);

        $ref->update([
            'first' => 'updated',
            'third' => 'new',
        ]);

        $expected = [
            'first' => 'updated',
            'second' => 'value',
            'third' => 'new',
        ];

        $this->assertEquals($expected, $ref->getValue());
    }

    public function testPush()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');

        $ref = $this->ref->getChild(__FUNCTION__);
        $value = 'a value';

        $newRef = $ref->push($value);

        $this->assertSame(1, $ref->getSnapshot()->numChildren());
        $this->assertSame($value, $newRef->getValue());
    }

    public function testRemove()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');

        $ref = $this->ref->getChild(__FUNCTION__);

        $ref->set([
            'first' => 'value',
            'second' => 'value',
        ]);

        $ref->getChild('first')->remove();

        $this->assertEquals(['second' => 'value'], $ref->getValue());
    }

    public function validValues()
    {
        return [
            'string' => 'value',
            'int' => 1,
            'bool_true' => true,
            'bool_false' => false,
            // 'array' => ['first' => 'value', 'second' => 'value'],
        ];
    }
}
