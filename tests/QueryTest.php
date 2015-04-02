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

class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Query
     */
    protected $query;

    protected function setUp()
    {
        $this->query = new Query();
    }

    public function testShallow()
    {
        $this->assertEquals('shallow=true', (string) $this->query->shallow());
        $this->assertEquals('', (string) $this->query->shallow(false));
    }

    public function testStartAt()
    {
        $this->assertEquals('startAt=%22foo%22', (string) $this->query->startAt('foo'));
        $this->assertEquals('startAt=%22White%20Space%22', (string) $this->query->startAt('White Space'));
    }

    public function testEndAt()
    {
        $this->assertEquals('endAt=%22foo%22', (string) $this->query->endAt('foo'));
        $this->assertEquals('endAt=%22White%20Space%22', (string) $this->query->endAt('White Space'));
    }

    public function testOrderBy()
    {
        $this->assertEquals('orderBy=%22foo%22', (string) $this->query->orderByChildKey('foo'));
        $this->assertEquals('orderBy=%22White%20Space%22', (string) $this->query->orderByChildKey('White Space'));
    }

    public function testOrderByKey()
    {
        $this->assertEquals('orderBy=%22%24key%22', (string) $this->query->orderByKey());
    }

    public function testOrderByPriority()
    {
        $this->assertEquals('orderBy=%22%24priority%22', (string) $this->query->orderByPriority());
    }

    public function testLimitToFirst()
    {
        $this->assertEquals('limitToFirst=2', (string) $this->query->limitToFirst(2));
    }

    public function testLimitToLast()
    {
        $this->assertEquals('limitToLast=2', (string) $this->query->limitToLast(2));
    }

    public function testMultipleLimitToSettingsShouldUseLastOne()
    {
        $this->query->limitToFirst(2);
        $this->query->limitToLast(3);

        $this->assertEquals('limitToLast=3', (string) $this->query);
    }
}
