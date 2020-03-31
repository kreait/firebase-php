<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Value\Email;
use Psr\Http\Message\UriInterface;

final class User
{
    /** @var string|null */
    private $name;

    /** @var Email|null */
    private $email;

    /** @var UriInterface|null */
    private $imageUri;

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
        $new->email = ($data['email'] ?? null) ? new Email($data['email']) : null;
        $new->imageUri = ($data['imageUrl'] ?? null) ? new Uri($data['imageUrl']) : null;

        return $new;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function email(): ?Email
    {
        return $this->email;
    }

    public function imageUri(): ?UriInterface
    {
        return $this->imageUri;
    }
}
