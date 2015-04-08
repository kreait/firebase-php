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

        $this->setUpQueryData();
    }

    protected function getLocation($subLocation = null)
    {
        return sprintf('%s/%s/%s', parent::getLocation(), 'query_test/users', $subLocation);
    }

    protected function setUpQueryData()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->firebase->set(['first_name' => 'd', 'height' => 1, '.priority' => 2], $this->getLocation('d'));
        $this->firebase->set(['first_name' => 'b', 'height' => 3, '.priority' => 4], $this->getLocation('b'));
        $this->firebase->set(['first_name' => 'c', 'height' => 2, '.priority' => 1], $this->getLocation('c'));
        $this->firebase->set(['first_name' => 'a', 'height' => 4, '.priority' => 3], $this->getLocation('a'));

        $this->recorder->stopRecording();
        $this->recorder->eject();
    }

    public function testEmptyQuery()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $result = $this->firebase->query($this->getLocation(), $this->query);

        $this->assertCount(4, $result);
        foreach ($result as $key => $value) {
            $this->assertNotTrue($value);
        }
    }

    public function testShallow()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->query->shallow(true);
        $result = $this->firebase->query($this->getLocation(), $this->query);

        foreach ($result as $key => $value) {
            $this->assertTrue($value);
        }
    }

    public function testOrderByKey()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->query->orderByKey();
        $result = $this->firebase->query($this->getLocation(), $this->query);

        $this->assertSame(['a', 'b', 'c', 'd'], array_keys($result));
    }

    public function testOrderByChildKey()
    {
        $this->markTestSkipped('Ordering by child key does not seem to be working right now.');
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->query->orderByChildKey('height');
        $result = $this->firebase->query($this->getLocation(), $this->query);

        $this->assertSame(['d', 'c', 'b', 'a'], array_keys($result));
    }

    /**
     * @expectedException \Kreait\Firebase\Exception\FirebaseException
     * @expectedExceptionMessage orderBy must be defined when other query parameters are defined
     */
    public function testStartAtWithoutOrderByThrowsException()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->query->startAt('b');
        $this->firebase->query($this->getLocation(), $this->query);
    }

    public function testStartAt()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->query->orderByKey();
        $this->query->startAt('b');
        $result = $this->firebase->query($this->getLocation(), $this->query);

        $this->assertCount(3, $result);
        $this->assertSame(['b', 'c', 'd'], array_keys($result));
    }

    public function testEndAt()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->query->orderByKey();
        $this->query->endAt('c');
        $result = $this->firebase->query($this->getLocation(), $this->query);

        $this->assertCount(3, $result);
        $this->assertSame(['a', 'b', 'c'], array_keys($result));
    }

    public function testOrderByPriority()
    {
        $this->markTestSkipped('Ordering by child key does not seem to be working right now.');
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->query->orderByPriority();
        $result = $this->firebase->query($this->getLocation(), $this->query);

        $this->assertCount(4, $result);
        $this->assertSame(['c', 'd', 'a', 'b'], array_keys($result));
    }

    public function testLimitToFirst()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->query->orderByKey();
        $this->query->limitToFirst(2);
        $result = $this->firebase->query($this->getLocation(), $this->query);

        $this->assertCount(2, $result);
        $this->assertSame(['a', 'b'], array_keys($result));
    }

    public function testLimitToLast()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->query->orderByKey();
        $this->query->limitToLast(2);
        $result = $this->firebase->query($this->getLocation(), $this->query);

        $this->assertCount(2, $result);
        $this->assertSame(['c', 'd'], array_keys($result));
    }

    public function testMultiple()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->query
            ->orderByKey()
            ->startAt('a')
            ->endAt('c');

        $result = $this->firebase->query($this->getLocation(), $this->query);

        $this->assertCount(3, $result);
        $this->assertSame(['a', 'b', 'c'], array_keys($result));
    }
}
