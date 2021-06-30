<?php

namespace Kreait\Firebase\IdentityPlatform;

use Kreait\Firebase\Exception\InvalidArgumentException;
use \Carbon\Carbon;
use JsonSerializable;
use Kreait\Firebase\Value\Certificate;

class SpCertificate implements JsonSerializable
{
    // @phpstan-ignore-next-line
    private Certificate $x509Certificate;
    // @phpstan-ignore-next-line
    private Carbon $expiresAt;

    public const FIELDS = ['x509Certificate', 'expiresAt', ];

    final private function __construct()
    {
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * @param array<string, mixed> $properties
     *
     * @throws InvalidArgumentException when invalid properties have been provided
     */
    public static function withProperties(array $properties): static
    {
        $instance = new static();

        foreach ($properties as $key => $value) {
            switch ($key) {
                case 'x509Certificate':
                    $instance->x509Certificate = $value instanceof Certificate ? $value : new Certificate($value);
                break;
                 case 'expiresAt':
                    $instance->expiresAt  = $value instanceof Carbon ? $value : new Carbon($value);
                    break;
                default:
                    throw new InvalidArgumentException(sprintf('%s is not a valid property', $key));
            }
        }

        return $instance;
    }
    /**
     * To Array
     *
     * @return array<String, mixed>
     */
    public function toArray() : array
    {
        return [
            'x509Certificate' => $this->x509Certificate,
            'expiresAt' => $this->expiresAt ?? null,
        ];
    }
    /**
     *
     * @return array<String, mixed>
     */
    public function jsonSerialize() : array
    {
        return \array_filter($this->toArray(), static fn ($value) => $value !== null);
    }
}
