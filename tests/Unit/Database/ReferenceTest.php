<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\UrlBuilder;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\OutOfRangeException;
use Kreait\Firebase\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
final class ReferenceTest extends UnitTestCase
{
    private ApiClient&MockObject $apiClient;
    private Reference $reference;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClient = $this->createMock(ApiClient::class);
        $url = 'https://project.example.com/parent/key';

        $this->reference = new Reference(
            new Uri($url),
            $this->apiClient,
            UrlBuilder::create($url),
        );
    }

    #[Test]
    public function getKey(): void
    {
        $this->assertSame('key', $this->reference->getKey());
    }

    #[Test]
    public function getPath(): void
    {
        $this->assertSame('parent/key', $this->reference->getPath());
    }

    #[Test]
    public function getParent(): void
    {
        $this->assertSame('parent', $this->reference->getParent()->getPath());
    }

    #[Test]
    public function getParentOfRoot(): void
    {
        $this->expectException(OutOfRangeException::class);

        $this->reference->getParent()->getParent();
    }

    #[Test]
    public function getRoot(): void
    {
        $root = $this->reference->getRoot();

        $this->assertSame('/', $root->getUri()->getPath());
    }

    #[Test]
    public function getChild(): void
    {
        $child = $this->reference->getChild('child');

        $this->assertSame('parent/key/child', $child->getPath());
    }

    #[Test]
    public function getInvalidChild(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->reference->getChild('#');
    }

    #[Test]
    public function getChildKeys(): void
    {
        $this->apiClient
            ->method('get')
            ->with($this->anything())
            ->willReturn(['a' => true, 'b' => true, 'c' => true])
        ;

        $this->assertSame(['a', 'b', 'c'], $this->reference->getChildKeys());
    }

    #[Test]
    public function getChildKeysWhenNoChildrenAreSet(): void
    {
        $this->apiClient
            ->method('get')
            ->with($this->anything())
            ->willReturn('scalar value')
        ;

        $this->expectException(OutOfRangeException::class);

        $this->reference->getChildKeys();
    }

    #[Test]
    public function getSnapshot(): void
    {
        $this->apiClient->method('get')->with($this->anything())->willReturn('value');

        $this->assertSame('value', $this->reference->getSnapshot()->getValue());
    }

    #[Test]
    public function getValue(): void
    {
        $this->apiClient->method('get')->with($this->anything())->willReturn('value');

        $this->assertSame('value', $this->reference->getValue());
    }

    #[Test]
    public function set(): void
    {
        $this->apiClient->expects($this->once())->method('set');

        $this->assertSame($this->reference, $this->reference->set('value'));
    }

    #[Test]
    public function remove(): void
    {
        $this->apiClient->expects($this->once())->method('remove');

        $this->assertSame($this->reference, $this->reference->remove());
    }

    #[Test]
    public function update(): void
    {
        $this->apiClient->expects($this->once())->method('update');

        $this->assertSame($this->reference, $this->reference->update(['any' => 'thing']));
    }

    #[Test]
    public function push(): void
    {
        $this->apiClient->expects($this->once())->method('push')->willReturn('newChild');

        $childReference = $this->reference->push('value');
        $this->assertSame('newChild', $childReference->getKey());
    }

    #[Test]
    public function getUri(): void
    {
        $uri = $this->reference->getUri();

        $this->assertSame((string) $uri, (string) $this->reference);
    }
}
