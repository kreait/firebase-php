<?php

declare(strict_types=1);

namespace Kreait\Tests\Integration\Database;

use Kreait\Firebase\Database\Reference;
use Kreait\Tests\IntegrationTestCase;

/**
 * @group Integration
 */
class ReferenceTest extends IntegrationTestCase
{
    /**
     * @var Reference
     */
    private static $ref;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$ref = self::$firebase->getDatabase()->getReference(self::$prefix)->getChild('Database');

        self::$ref->remove();
    }

    /**
     * @param $key
     * @param $value
     *
     * @dataProvider validValues
     */
    public function testSetAndGet($key, $value)
    {
        $ref = self::$ref->getChild(__FUNCTION__.'/'.$key);
        $ref->set($value);

        $this->assertSame($value, $ref->getValue());
    }

    public function testUpdate()
    {
        $ref = self::$ref->getChild(__FUNCTION__);
        $ref->set([
            'first' => 'value',
            'second' => 'value'
        ]);

        $ref->update([
            'first' => 'updated',
            'third' => 'new'
        ]);

        $expected = [
            'first' => 'updated',
            'second' => 'value',
            'third' => 'new'
        ];

        $this->assertEquals($expected, $ref->getValue());
    }

    public function testPush()
    {
        $ref = self::$ref->getChild(__FUNCTION__);
        $value = 'a value';

        $newRef = $ref->push($value);

        $this->assertSame(1, $ref->getSnapshot()->numChildren());
        $this->assertSame($value, $newRef->getValue());
    }

    public function testRemove()
    {
        $ref = self::$ref->getChild(__FUNCTION__);

        $ref->set([
            'first' => 'value',
            'second' => 'value'
        ]);

        $ref->getChild('first')->remove();

        $this->assertEquals(['second' => 'value'], $ref->getValue());
    }

    public function validValues()
    {
        return [
            'string' => ['string', 'value'],
            'int' => ['int', 1],
            'bool_true' => ['true', true],
            'bool_false' => ['false', false],
            'array' => ['array', ['first' => 'value', 'second' => 'value']],
        ];
    }
}