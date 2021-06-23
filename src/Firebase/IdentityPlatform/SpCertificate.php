<?php

namespace Kreait\Firebase\IdentityPlatform;

use Kreait\Firebase\Exception\InvalidArgumentException;
use \Carbon\Carbon;
use JsonSerializable;
use Kreait\Firebase\Value\Certificate;

class SpCertificate implements JsonSerializable
{
    private Certificate $x509Certificate;
    private Carbon $expiresAt;

    public const FIELDS = ['x509Certificate', 'expiresAt', ];

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    /**
     * @param array<string, mixed> $properties
     *
     * @throws InvalidArgumentException when invalid properties have been provided
     */
    public static function withProperties(array $properties): self
    {
        $instance = new self();

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

    public function toArray()
    {
        return [
            'x509Certificate' => $this->x509Certificate,
            'expiresAt' => $this->expiresAt,
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
