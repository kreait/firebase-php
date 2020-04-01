<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\Query;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Snapshot;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\OutOfRangeException;
use Kreait\Firebase\Tests\UnitTestCase;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 */
class ReferenceTest extends UnitTestCase
{
    /**
     * @var Uri
     */
    private $uri;

    /**
     * @var ApiClient|\PHPUnit\Framework\MockObject\MockObject
     */
    private $apiClient;

    /**
     * @var Reference
     */
    private $reference;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uri = new Uri('http://domain.tld/parent/key');
        $this->apiClient = $this->createMock(ApiClient::class);

        $this->reference = new Reference($this->uri, $this->apiClient);
    }

    public function testGetKey()
    {
        $this->assertSame('key', $this->reference->getKey());
    }

    public function testGetPath()
    {
        $this->assertSame('parent/key', $this->reference->getPath());
    }

    public function testGetParent()
    {
        $this->assertSame('parent', $this->reference->getParent()->getPath());
    }

    public function testGetParentOfRoot()
    {
        $this->expectException(OutOfRangeException::class);

        $this->reference->getParent()->getParent();
    }

    public function testGetRoot()
    {
        $root = $this->reference->getRoot();

        $this->assertSame('/', $root->getUri()->getPath());
    }

    public function testGetChild()
    {
        $child = $this->reference->getChild('child');

        $this->assertSame('parent/key/child', $child->getPath());
    }

    public function testGetInvalidChild()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->reference->getChild('#');
    }

    public function testGetChildKeys()
    {
        $this->apiClient
            ->expects($this->any())
            ->method('get')
            ->with($this->anything())
            ->willReturn(['a' => true, 'b' => true, 'c' => true]);

        $this->assertSame(['a', 'b', 'c'], $this->reference->getChildKeys());
    }

    public function testGetChildKeysWhenNoChildrenAreSet()
    {
        $this->apiClient
            ->expects($this->any())
            ->method('get')
            ->with($this->anything())
            ->willReturn('scalar value');

        $this->expectException(OutOfRangeException::class);

        $this->reference->getChildKeys();
    }

    public function testModifiersReturnQueries()
    {
        $this->assertInstanceOf(Query::class, $this->reference->equalTo('x'));
        $this->assertInstanceOf(Query::class, $this->reference->endAt('x'));
        $this->assertInstanceOf(Query::class, $this->reference->limitToFirst(1));
        $this->assertInstanceOf(Query::class, $this->reference->limitToLast(1));
        $this->assertInstanceOf(Query::class, $this->reference->orderByChild('child'));
        $this->assertInstanceOf(Query::class, $this->reference->orderByKey());
        $this->assertInstanceOf(Query::class, $this->reference->orderByValue());
        $this->assertInstanceOf(Query::class, $this->reference->shallow());
        $this->assertInstanceOf(Query::class, $this->reference->startAt('x'));
    }

    public function testGetSnapshot()
    {
        $this->apiClient->expects($this->any())->method('get')->with($this->anything())->willReturn('value');

        $this->assertInstanceOf(Snapshot::class, $this->reference->getSnapshot());
    }

    public function testGetValue()
    {
        $this->apiClient->expects($this->any())->method('get')->with($this->anything())->willReturn('value');

        $this->assertSame('value', $this->reference->getValue());
    }

    public function testSet()
    {
        $this->apiClient->expects($this->once())->method('set');

        $this->assertSame($this->reference, $this->reference->set('value'));
    }

    public function testRemove()
    {
        $this->apiClient->expects($this->once())->method('remove');

        $this->assertSame($this->reference, $this->reference->remove());
    }

    public function testUpdate()
    {
        $this->apiClient->expects($this->once())->method('update');

        $this->assertSame($this->reference, $this->reference->update(['any' => 'thing']));
    }

    public function testPush()
    {
        $this->apiClient->expects($this->once())->method('push')->willReturn('newChild');

        $childReference = $this->reference->push('value');
        $this->assertSame('newChild', $childReference->getKey());
    }

    public function testGetUri()
    {
        $uri = $this->reference->getUri();

        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertSame((string) $uri, (string) $this->reference);
    }
}
