<?php

declare(strict_types=1);

namespace Kreait\Firebase\IdentityPlatform;

use InvalidArgumentException;
use Kreait\Firebase\Value\Url;

class SpConfig implements \JsonSerializable
{
    // @phpstan-ignore-next-line
    private string $spEntityId;
    // @phpstan-ignore-next-line
    private Url $callbackUri;
    /**
     * @var array<SpCertificate>
     */
    private array $spCertificates;

    public const FIELDS = ['spEntityId', 'callbackUri', 'spCertificates'];

    private function __construct()
    {
        $this->spCertificates = [];
    }

    // @phpstan-ignore-next-line (php 8 you can return static instead of self)
    public static function new()
    {
        return new self();
    }

    /**
     * @param array<string, mixed> $properties
     *
     * @throws InvalidArgumentException when invalid properties have been provided
     *  @phpstan-ignore-next-line (php 8 you can return static instead of self)
     */
    public static function withProperties(array $properties)
    {
        $instance = new self();

        foreach ($properties as $key => $value) {
            switch ($key) {
                case 'spEntityId':
                    $instance->spEntityId = $value;

                    break;

                case 'callbackUri':
                    $instance->callbackUri = $value instanceof Url ? $value : Url::fromValue($value);

                    break;

                case 'spCertificates':
                    if (!\is_array($value)) {
                        throw new InvalidArgumentException(\sprintf('%s must be an array', $key));
                    }
                    $instance->spCertificates = \array_map(fn ($certificate) => $certificate instanceof SpCertificate ? $certificate : SpCertificate::withProperties($certificate), $value);

                    break;

                default:
                 throw new InvalidArgumentException(\sprintf('%s is not a valid property', $key));
            }
        }

        return $instance;
    }

    /**
     * @return array<String, mixed>
     */
    public function toArray(): array
    {
        return [
            'spEntityId' => $this->spEntityId,
            'callbackUri' => $this->callbackUri,
            'spCertificates' => $this->spCertificates ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return \array_filter($this->toArray(), static fn ($value) => $value !== null);
    }

    public function __get(string $name)
    {
        if (!isset(self::FIELDS[$name])) {
            return trigger_error("Property $name doesn't exists and cannot be set.", E_USER_ERROR);
        }

        return $this->$name ?? null;
    }
}
