<?php

declare(strict_types=1);

namespace Kreait\Firebase\IdentityPlatform;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\IdentityPlatform\ParsesName;

class DefaultSupportedIdpConfig
{
    use ParsesName;
    // @phpstan-ignore-next-line
    private string $name;
    // @phpstan-ignore-next-line
    private ?bool $enabled;
    // @phpstan-ignore-next-line
    private ?string $clientId;
    // @phpstan-ignore-next-line
    private ?string $clientSecret;

    public const FIELDS = ['name', 'enabled', 'clientId', 'clientSecret'];

    final private function __construct()
    {
    }

    public static function new(): self
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

        if (!$name = $properties['name'] ?? null) {
            throw new InvalidArgumentException('name property is a required string');
        }
        foreach ($properties as $key => $value) {
            switch ($key) {
                case 'name':
                    $instance->name = static::parseName($value);
                break;
                case 'enabled':
                    if (!is_bool($value)) {
                        throw new InvalidArgumentException(sprintf('%s is not a valid property', $key));
                    }
                    // no break
                case 'clientId':
                case 'clientSecret':
                    $instance->$key = $value;

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
     * @return array<String, bool|string|null>
     */
    public function toArray(): array
    {
        return [
                'name' => $this->getName(),
                'enabled' => $this->enabled ?? null,
                'clientId' => $this->clientId ?? null,
                'clientSecret' => $this->clientSecret ?? null,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function validateName(string $name): bool
    {
        return true;
    }
}