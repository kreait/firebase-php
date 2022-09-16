<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\Query;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Snapshot;
use Kreait\Firebase\Database\UrlBuilder;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\OutOfRangeException;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 */
final class ReferenceTest extends UnitTestCase
{
    /**
     * @var ApiClient|MockObject
     */
    private $apiClient;
    private Reference $reference;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClient = $this->createMock(ApiClient::class);
        $url = 'https://project.domain.tld/parent/key';

        $this->reference = new Reference(
            new Uri($url),
            $this->apiClient,
            UrlBuilder::create($url),
        );
    }

    public function testGetKey(): void
    {
        self::assertSame('key', $this->reference->getKey());
    }

    public function testGetPath(): void
    {
        self::assertSame('parent/key', $this->reference->getPath());
    }

    public function testGetParent(): void
    {
        self::assertSame('parent', $this->reference->getParent()->getPath());
    }

    public function testGetParentOfRoot(): void
    {
        $this->expectException(OutOfRangeException::class);

        $this->reference->getParent()->getParent();
    }

    public function testGetRoot(): void
    {
        $root = $this->reference->getRoot();

        self::assertSame('/', $root->getUri()->getPath());
    }

    public function testGetChild(): void
    {
        $child = $this->reference->getChild('child');

        self::assertSame('parent/key/child', $child->getPath());
    }

    public function testGetInvalidChild(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->reference->getChild('#');
    }

    public function testGetChildKeys(): void
    {
        $this->apiClient
            ->method('get')
            ->with(self::anything())
            ->willReturn(['a' => true, 'b' => true, 'c' => true]);

        self::assertSame(['a', 'b', 'c'], $this->reference->getChildKeys());
    }

    public function testGetChildKeysWhenNoChildrenAreSet(): void
    {
        $this->apiClient
            ->method('get')
            ->with(self::anything())
            ->willReturn('scalar value');

        $this->expectException(OutOfRangeException::class);

        $this->reference->getChildKeys();
    }

    public function testModifiersReturnQueries(): void
    {
        self::assertInstanceOf(Query::class, $this->reference->equalTo('x'));
        self::assertInstanceOf(Query::class, $this->reference->endAt('x'));
        self::assertInstanceOf(Query::class, $this->reference->endBefore('x'));
        self::assertInstanceOf(Query::class, $this->reference->limitToFirst(1));
        self::assertInstanceOf(Query::class, $this->reference->limitToLast(1));
        self::assertInstanceOf(Query::class, $this->reference->orderByChild('child'));
        self::assertInstanceOf(Query::class, $this->reference->orderByKey());
        self::assertInstanceOf(Query::class, $this->reference->orderByValue());
        self::assertInstanceOf(Query::class, $this->reference->shallow());
        self::assertInstanceOf(Query::class, $this->reference->startAt('x'));
        self::assertInstanceOf(Query::class, $this->reference->startAfter('x'));
    }

    public function testGetSnapshot(): void
    {
        $this->apiClient->method('get')->with(self::anything())->willReturn('value');

        self::assertInstanceOf(Snapshot::class, $this->reference->getSnapshot());
    }

    public function testGetValue(): void
    {
        $this->apiClient->method('get')->with(self::anything())->willReturn('value');

        self::assertSame('value', $this->reference->getValue());
    }

    public function testSet(): void
    {
        $this->apiClient->expects(self::once())->method('set');

        self::assertSame($this->reference, $this->reference->set('value'));
    }

    public function testRemove(): void
    {
        $this->apiClient->expects(self::once())->method('remove');

        self::assertSame($this->reference, $this->reference->remove());
    }

    public function testUpdate(): void
    {
        $this->apiClient->expects(self::once())->method('update');

        self::assertSame($this->reference, $this->reference->update(['any' => 'thing']));
    }

    public function testPush(): void
    {
        $this->apiClient->expects(self::once())->method('push')->willReturn('newChild');

        $childReference = $this->reference->push('value');
        self::assertSame('newChild', $childReference->getKey());
    }

    public function testGetUri(): void
    {
        $uri = $this->reference->getUri();

        self::assertInstanceOf(UriInterface::class, $uri);
        self::assertSame((string) $uri, (string) $this->reference);
    }
}
