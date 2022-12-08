<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Beste\Json;
use GuzzleHttp\Psr7\Utils;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use function trim;

/**
 * @see https://github.com/googleapis/google-api-nodejs-client/blob/main/src/apis/firebasedynamiclinks/v1.ts
 *
 * @phpstan-type DynamicLinkWarningShape array{
 *     warningCode?: non-empty-string,
 *     warningDocumentLink?: non-empty-string,
 *     warningMessage?: non-empty-string
 * }
 * @phpstan-type DynamicLinkShape array{
 *     shortLink: non-empty-string,
 *     previewLink?: non-empty-string,
 *     warning?: list<DynamicLinkWarningShape>
 * }
 */
final class DynamicLink implements JsonSerializable
{
    /**
     * @param DynamicLinkShape $data
     */
    private function __construct(private readonly array $data)
    {
    }

    public function __toString(): string
    {
        return (string) $this->uri();
    }

    /**
     * @internal
     */
    public static function fromApiResponse(ResponseInterface $response): self
    {
        return new self(Json::decode((string) $response->getBody(), true));
    }

    public function uri(): UriInterface
    {
        return Utils::uriFor($this->data['shortLink']);
    }

    public function previewUri(): ?UriInterface
    {
        $previewLink = $this->data['previewLink'] ?? null;

        return $previewLink !== null ? Utils::uriFor($previewLink) : null;
    }

    /**
     * @return non-empty-string
     */
    public function domain(): string
    {
        $uri = $this->uri();

        return $uri->getScheme().'://'.$uri->getHost();
    }

    public function suffix(): string
    {
        return trim($this->uri()->getPath(), '/');
    }

    /**
     * @return list<DynamicLinkWarningShape>
     */
    public function warnings(): array
    {
        return $this->data['warning'] ?? [];
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings());
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
