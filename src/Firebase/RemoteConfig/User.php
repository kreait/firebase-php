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
     */
    public static function fromArray(array $data): self
    {
        $new = new self();
        $new->name = $data['name'] ?? null;
        $new->email = ($data['email'] ?? null) ? new Email($data['email']) : null;
        $new->imageUri = ($data['imageUrl'] ?? null) ? new Uri($data['imageUrl']) : null;

        return $new;
    }

    /**
     * @return string|null
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return Email|null
     */
    public function email()
    {
        return $this->email;
    }

    /**
     * @return UriInterface|null
     */
    public function imageUri()
    {
        return $this->imageUri;
    }
}
