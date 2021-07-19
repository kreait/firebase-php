<?php

declare(strict_types=1);

namespace Kreait\Firebase\IdentityPlatform;

use DateTimeImmutable;
use JsonSerializable;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Certificate;
use Kreait\Firebase\Util\DT;

class SpCertificate implements JsonSerializable
{
    // @phpstan-ignore-next-line
    private Certificate $x509Certificate;
    // @phpstan-ignore-next-line
    private DateTimeImmutable $expiresAt;

    public const FIELDS = ['x509Certificate', 'expiresAt'];

    final private function __construct()
    {
    }
    // @phpstan-ignore-next-line (php 8 you can return static instead of self)
    public static function new()
    {
        return new static();
    }

    /**
     * @param array<string, mixed> $properties
     *
     * @throws InvalidArgumentException when invalid properties have been provided
     * @phpstan-ignore-next-line (php 8 you can return static instead of self)
     */
    public static function withProperties(array $properties)
    {
        $instance = new static();

        foreach ($properties as $key => $value) {
            switch ($key) {
                case 'x509Certificate':
                    $instance->x509Certificate = $value instanceof Certificate ? $value : new Certificate($value);
                break;

                 case 'expiresAt':
                    $instance->expiresAt = $value instanceof DateTimeImmutable ? $value : DT::toUTCDateTimeImmutable($value);
                    break;

                default:
                    throw new InvalidArgumentException(\sprintf('%s is not a valid property', $key));
            }
        }

        if (!isset($instance->x509Certificate) || !isset($instance->expiresAt)) {
            throw new InvalidArgumentException('x509Certificate and expiresAt are required propertries');
        }

        return $instance;
    }

    /**
     * To Array.
     *
     * @return array<String, mixed>
     */
    public function toArray(): array
    {
        return [
            'x509Certificate' => $this->x509Certificate,
            'expiresAt' => isset($this->expiresAt) ? $this->expiresAt->format(\DATE_ATOM) : null
        ];
    }

    /**
     * @return array<String, mixed>
     */
    public function jsonSerialize(): array
    {
        return \array_filter($this->toArray(), static fn ($value) => $value !== null);
    }
}
