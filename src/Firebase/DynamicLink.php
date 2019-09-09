<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use function GuzzleHttp\Psr7\uri_for;
use JsonSerializable;
use Kreait\Firebase\Util\JSON;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

final class DynamicLink implements JsonSerializable
{
    /** @var array */
    private $data = [];

    private function __construct()
    {
    }

    /**
     * @internal
     */
    public static function fromApiResponse(ResponseInterface $response): self
    {
        $link = new self();
        $link->data = JSON::decode((string) $response->getBody(), true);

        return $link;
    }

    public function uri(): UriInterface
    {
        return uri_for($this->data['shortLink']);
    }

    public function previewUri(): UriInterface
    {
        return uri_for($this->data['previewLink']);
    }

    public function domain(): string
    {
        return $this->uri()->getScheme().'://'.$this->uri()->getHost();
    }

    public function suffix(): string
    {
        return \trim($this->uri()->getPath(), '/');
    }

    public function warnings(): array
    {
        return $this->data['warning'] ?? [];
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings());
    }

    public function jsonSerialize()
    {
        return $this->data;
    }

    public function __toString()
    {
        return (string) $this->uri();
    }
}
