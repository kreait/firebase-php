<?php
/**
 * This file is part of the firebase-php package.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
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

        $this->baseLocation = 'query_test/users';
        $this->setHttpAdapter();

        $this->query = new Query();

        $this->setUpQueryData();
    }

    protected function setUpQueryData()
    {
        $this->recorder->insertTape(__FUNCTION__);
        $this->recorder->startRecording();

        $this->firebase->set(['first_name' => 'd', 'height' => 1, '.priority' => 2], $this->baseLocation.'/d');
        $this->firebase->set(['first_name' => 'b', 'height' => 3, '.priority' => 4], $this->baseLocation.'/b');
        $this->firebase->set(['first_name' => 'c', 'height' => 2, '.priority' => 1], $this->baseLocation.'/c');
        $this->firebase->set(['first_name' => 'a', 'height' => 4, '.priority' => 3], $this->baseLocation.'/a');

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
        $this->markTestSkipped("Ordering by child key does not seem to be working right now.");
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
        $this->markTestSkipped("Ordering by child key does not seem to be working right now.");
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
