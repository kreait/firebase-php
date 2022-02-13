<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Beste\Json;
use GuzzleHttp\Psr7\Utils;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

final class DynamicLink implements JsonSerializable
{
    /** @var array<string, mixed> */
    private array $data = [];

    private function __construct()
    {
    }

    /**
     * @internal
     */
    public static function fromApiResponse(ResponseInterface $response): self
    {
        $link = new self();
        $link->data = Json::decode((string) $response->getBody(), true);

        return $link;
    }

    public function uri(): UriInterface
    {
        return Utils::uriFor($this->data['shortLink']);
    }

    public function previewUri(): UriInterface
    {
        return Utils::uriFor($this->data['previewLink']);
    }

    public function domain(): string
    {
        return $this->uri()->getScheme().'://'.$this->uri()->getHost();
    }

    public function suffix(): string
    {
        return \trim($this->uri()->getPath(), '/');
    }

    /**
     * @return string[]
     */
    public function warnings(): array
    {
        return $this->data['warning'] ?? [];
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings());
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    public function __toString(): string
    {
        return (string) $this->uri();
    }
}
