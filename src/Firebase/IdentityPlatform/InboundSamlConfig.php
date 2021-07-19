<?php

declare(strict_types=1);

namespace Kreait\Firebase\IdentityPlatform;

use Kreait\Firebase\Exception\InvalidArgumentException;

class InboundSamlConfig
{
    use ParsesName;
    // @phpstan-ignore-next-line
    private string $name;
    private ?IdpConfig $idpConfig = null;
    private ?SpConfig $spConfig = null;
    private ?string $displayName = null;
    private ?bool $enabled = null;

    public const FIELDS = ['name', 'idpConfig', 'spConfig', 'displayName', 'enabled'];

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

        if (!isset($properties['name'])) {
            throw new InvalidArgumentException('name property is a required string');
        }

        foreach ($properties as $key => $value) {
            switch ($key) {
                case 'name':
                    $name = static::parseName($value);
                    $instance->name = $name;

                    break;

                case 'idpConfig':
                    $instance->idpConfig = $value instanceof IdpConfig ? $value : IdpConfig::withProperties($value);

                    break;

                case 'spConfig':
                    $instance->spConfig = $value instanceof SpConfig ? $value : SpConfig::withProperties($value);

                     break;

                case 'displayName':
                    $instance->displayName = $value;

                    break;

                case 'enabled':
                    if (!\is_bool($value)) {
                        throw new InvalidArgumentException(\sprintf('%s must be a boolean', $key));
                    }
                    $instance->enabled = $value;

                    break;

                default:
                    throw new InvalidArgumentException(\sprintf('%s is not a valid property', $key));
            }
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
            'name' => $this->name,
            'idpConfig' => $this->idpConfig,
            'spConfig' => $this->spConfig,
            'displayName' => $this->displayName ?? null,
            'enabled' => $this->enabled ?? null,
        ];
    }

    public static function validateName(string $name): bool
    {
        if (\stripos($name, 'saml.') !== 0) {
            throw new InvalidArgumentException('name property is must start with "saml."');
        }

        return true;
    }
}
