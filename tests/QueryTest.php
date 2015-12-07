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

    public function testStartAt()
    {
        $this->assertStringEndsWith('startAt=%22foo%22', (string) $this->query->startAt('foo'));
        $this->assertStringEndsWith('startAt=%22White%20Space%22', (string) $this->query->startAt('White Space'));
    }

    public function testEndAt()
    {
        $this->assertStringEndsWith('endAt=%22foo%22', (string) $this->query->endAt('foo'));
        $this->assertStringEndsWith('endAt=%22White%20Space%22', (string) $this->query->endAt('White Space'));
    }

    public function testOrderBy()
    {
        $this->assertStringEndsWith('orderBy=%22foo%22', (string) $this->query->orderByChildKey('foo'));
        $this->assertStringEndsWith('orderBy=%22White%20Space%22', (string) $this->query->orderByChildKey('White Space'));
    }

    public function testOrderByKey()
    {
        $this->assertStringEndsWith('orderBy=%22%24key%22', (string) $this->query->orderByKey());
    }

    public function testOrderByPriority()
    {
        $this->assertStringEndsWith('orderBy=%22%24priority%22', (string) $this->query->orderByPriority());
    }

    public function testLimitToFirst()
    {
        $this->assertStringEndsWith('limitToFirst=2', (string) $this->query->limitToFirst(2));
    }

    public function testLimitToLast()
    {
        $this->assertStringEndsWith('limitToLast=2', (string) $this->query->limitToLast(2));
    }

    public function testMultipleLimitToSettingsShouldUseLastOne()
    {
        $this->query->limitToFirst(2);
        $this->query->limitToLast(3);

        $this->assertStringEndsWith('limitToLast=3', (string) $this->query);
    }

    public function testShallow()
    {
        $this->query->shallow(true);

        $this->assertStringEndsWith('shallow=true', (string) $this->query);
    }
}
