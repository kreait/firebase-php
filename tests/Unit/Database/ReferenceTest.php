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
    /** @var ApiClient|MockObject */
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
        $this->assertSame('key', $this->reference->getKey());
    }

    public function testGetPath(): void
    {
        $this->assertSame('parent/key', $this->reference->getPath());
    }

    public function testGetParent(): void
    {
        $this->assertSame('parent', $this->reference->getParent()->getPath());
    }

    public function testGetParentOfRoot(): void
    {
        $this->expectException(OutOfRangeException::class);

        $this->reference->getParent()->getParent();
    }

    public function testGetRoot(): void
    {
        $root = $this->reference->getRoot();

        $this->assertSame('/', $root->getUri()->getPath());
    }

    public function testGetChild(): void
    {
        $child = $this->reference->getChild('child');

        $this->assertSame('parent/key/child', $child->getPath());
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
            ->with($this->anything())
            ->willReturn(['a' => true, 'b' => true, 'c' => true]);

        $this->assertSame(['a', 'b', 'c'], $this->reference->getChildKeys());
    }

    public function testGetChildKeysWhenNoChildrenAreSet(): void
    {
        $this->apiClient
            ->method('get')
            ->with($this->anything())
            ->willReturn('scalar value');

        $this->expectException(OutOfRangeException::class);

        $this->reference->getChildKeys();
    }

    public function testGetSnapshot(): void
    {
        $this->apiClient->method('get')->with($this->anything())->willReturn('value');

        $this->assertInstanceOf(Snapshot::class, $this->reference->getSnapshot());
    }

    public function testGetValue(): void
    {
        $this->apiClient->method('get')->with($this->anything())->willReturn('value');

        $this->assertSame('value', $this->reference->getValue());
    }

    public function testSet(): void
    {
        $this->apiClient->expects($this->once())->method('set');

        $this->assertSame($this->reference, $this->reference->set('value'));
    }

    public function testRemove(): void
    {
        $this->apiClient->expects($this->once())->method('remove');

        $this->assertSame($this->reference, $this->reference->remove());
    }

    public function testUpdate(): void
    {
        $this->apiClient->expects($this->once())->method('update');

        $this->assertSame($this->reference, $this->reference->update(['any' => 'thing']));
    }

    public function testPush(): void
    {
        $this->apiClient->expects($this->once())->method('push')->willReturn('newChild');

        $childReference = $this->reference->push('value');
        $this->assertSame('newChild', $childReference->getKey());
    }

    public function testGetUri(): void
    {
        $uri = $this->reference->getUri();

        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertSame((string) $uri, (string) $this->reference);
    }
}
