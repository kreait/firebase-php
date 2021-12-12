<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

final class User
{
    private ?string $name = null;
    private ?string $email = null;
    private ?UriInterface $imageUri = null;

    private function __construct()
    {
    }

    /**
     * @internal
     *
     * @param array<string, string> $data
     */
    public static function fromArray(array $data): self
    {
        $new = new self();
        $new->name = $data['name'] ?? null;
        $new->email = ($data['email'] ?? null) ? ((string) $data['email']) : null;
        $new->imageUri = ($data['imageUrl'] ?? null) ? new Uri($data['imageUrl']) : null;

        return $new;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function imageUri(): ?UriInterface
    {
        return $this->imageUri;
    }
}
