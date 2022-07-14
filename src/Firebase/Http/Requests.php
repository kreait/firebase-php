<?php

declare(strict_types=1);

namespace Kreait\Firebase\Http;

use IteratorAggregate;
use Psr\Http\Message\RequestInterface;
use Traversable;

/**
 * @internal
 * @implements IteratorAggregate<RequestInterface>
 */
final class Requests implements IteratorAggregate
{
    /** @var RequestInterface[] */
    private array $requests;

    public function __construct(RequestInterface ...$requests)
    {
        $this->requests = $requests;
    }

    public function findByContentId(string $contentId): ?RequestInterface
    {
        foreach ($this->requests as $request) {
            $contentIdHeader = $request->getHeaderLine('Content-ID');
            $contentIdHeaderParts = \explode('-', $contentIdHeader);
            $requestContentId = \array_pop($contentIdHeaderParts);

            if ($contentId === $requestContentId) {
                return $request;
            }
        }

        return null;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return Traversable<RequestInterface>|RequestInterface[]
     */
    public function getIterator(): Traversable
    {
        yield from $this->requests;
    }
}
