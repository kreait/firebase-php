<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

/**
 * @phpstan-type RemoteConfigUserShape array{
 *     name?: non-empty-string,
 *     email?: non-empty-string,
 *     imageUrl?: non-empty-string
 * }
 */
final class User
{
    /**
     * @var non-empty-string|null
     */
    private ?string $name;

    /**
     * @var non-empty-string|null
     */
    private ?string $email;
    private ?UriInterface $imageUri;

    /**
     * @param non-empty-string|null $name
     * @param non-empty-string|null $email
     */
    private function __construct(?string $name, ?string $email, ?UriInterface $imageUri)
    {
        $this->name = $name;
        $this->email = $email;
        $this->imageUri = $imageUri;
    }

    /**
     * @internal
     *
     * @param RemoteConfigUserShape $data
     */
    public static function fromArray(array $data): self
    {
        $imageUrl = $data['imageUrl'] ?? null;
        $imageUri = $imageUrl ? new Uri($imageUrl) : null;

        return new self(
            $data['name'] ?? null,
            $data['email'] ?? null,
            $imageUri,
        );
    }

    /**
     * @return non-empty-string|null
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * @return non-empty-string|null
     */
    public function email(): ?string
    {
        return $this->email;
    }

    public function imageUri(): ?UriInterface
    {
        return $this->imageUri;
    }
}
