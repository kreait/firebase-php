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

class QueryIntegrationTest extends IntegrationTest
{
    /**
     * @var Query
     */
    protected $query;

    protected function setUp()
    {
        parent::setUp();

        $this->query = new Query();
    }

    protected function setUpQueryData($method)
    {
        $this->firebase->set([
            'd' => ['first_name' => 'd', 'height' => 1, '.priority' => 2],
            'b' => ['first_name' => 'b', 'height' => 3, '.priority' => 4],
            'c' => ['first_name' => 'c', 'height' => 2, '.priority' => 1],
            'a' => ['first_name' => 'a', 'height' => 4, '.priority' => 3],
        ], $this->getLocation($method));
    }

    protected function tearDownQueryData($method)
    {
        $this->firebase->delete($this->getLocation($method));
    }

    public function testEmptyQuery()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->setUpQueryData(__FUNCTION__);

        $result = $this->firebase->query($this->getLocation(__FUNCTION__), $this->query);

        $this->assertCount(4, $result);
        foreach ($result as $key => $value) {
            $this->assertNotTrue($value);
        }

        $this->tearDownQueryData(__FUNCTION__);
    }

    public function testOrderByKey()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->setUpQueryData(__FUNCTION__);

        $this->query->orderByKey();
        $result = $this->firebase->query($this->getLocation(__FUNCTION__), $this->query);

        $this->assertSame(['a', 'b', 'c', 'd'], array_keys($result));

        $this->tearDownQueryData(__FUNCTION__);
    }

    public function testOrderByChildKey()
    {
        $this->markTestSkipped('Ordering by child key does not seem to be working right now.');
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->setUpQueryData(__FUNCTION__);

        $this->query->orderByChildKey('height');
        $result = $this->firebase->query($this->getLocation(__FUNCTION__), $this->query);

        $this->assertSame(['d', 'c', 'b', 'a'], array_keys($result));

        $this->tearDownQueryData(__FUNCTION__);
    }

    public function testStartAt()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->setUpQueryData(__FUNCTION__);

        $this->query->orderByKey();
        $this->query->startAt('b');
        $result = $this->firebase->query($this->getLocation(__FUNCTION__), $this->query);

        $this->assertCount(3, $result);
        $this->assertSame(['b', 'c', 'd'], array_keys($result));

        $this->tearDownQueryData(__FUNCTION__);
    }

    public function testEndAt()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->setUpQueryData(__FUNCTION__);

        $this->query->orderByKey();
        $this->query->endAt('c');
        $result = $this->firebase->query($this->getLocation(__FUNCTION__), $this->query);

        $this->assertCount(3, $result);
        $this->assertSame(['a', 'b', 'c'], array_keys($result));

        $this->tearDownQueryData(__FUNCTION__);
    }

    public function testOrderByPriority()
    {
        $this->markTestSkipped('Ordering by child key does not seem to be working right now.');
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->setUpQueryData(__FUNCTION__);

        $this->query->orderByPriority();
        $result = $this->firebase->query($this->getLocation(__FUNCTION__), $this->query);

        $this->assertCount(4, $result);
        $this->assertSame(['c', 'd', 'a', 'b'], array_keys($result));

        $this->tearDownQueryData(__FUNCTION__);
    }

    public function testLimitToFirst()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->setUpQueryData(__FUNCTION__);

        $this->query->orderByKey();
        $this->query->limitToFirst(2);
        $result = $this->firebase->query($this->getLocation(__FUNCTION__), $this->query);

        $this->assertCount(2, $result);
        $this->assertSame(['a', 'b'], array_keys($result));

        $this->tearDownQueryData(__FUNCTION__);
    }

    public function testLimitToLast()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->setUpQueryData(__FUNCTION__);

        $this->query->orderByKey();
        $this->query->limitToLast(2);
        $result = $this->firebase->query($this->getLocation(__FUNCTION__), $this->query);

        $this->assertCount(2, $result);
        $this->assertSame(['c', 'd'], array_keys($result));

        $this->tearDownQueryData(__FUNCTION__);
    }

    public function testMultiple()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->setUpQueryData(__FUNCTION__);

        $this->query
            ->orderByKey()
            ->startAt('a')
            ->endAt('c');

        $result = $this->firebase->query($this->getLocation(__FUNCTION__), $this->query);

        $this->assertCount(3, $result);
        $this->assertSame(['a', 'b', 'c'], array_keys($result));

        $this->tearDownQueryData(__FUNCTION__);
    }

    public function testShallow()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->setUpQueryData(__FUNCTION__);

        $this->query
            ->orderByKey()
            ->shallow(true);

        $result = $this->firebase->query($this->getLocation(__FUNCTION__), $this->query);

        $this->assertEquals(['a' => true, 'b' => true, 'c' => true, 'd' => true], $result);

        $this->tearDownQueryData(__FUNCTION__);
    }
}
