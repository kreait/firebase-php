<?php

declare(strict_types=1);

namespace Kreait\Firebase\IdentityPlatform;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Url;

class OAuthIdpConfig
{
    use ParsesName;
    // @phpstan-ignore-next-line
    private string $name;
    // @phpstan-ignore-next-line
    private ?Url $issuer;
    // @phpstan-ignore-next-line
    private ?string $displayName;
    // @phpstan-ignore-next-line
    private ?bool $enabled;
    // @phpstan-ignore-next-line
    private ?string $clientId;
    // @phpstan-ignore-next-line
    private ?string $clientSecret;
    // @phpstan-ignore-next-line
    private ?OAuthResponseType $responseType;

    public const FIELDS = ['name', 'displayName', 'enabled', 'clientId', 'clientSecret', 'responseType'];

    final private function __construct()
    {
    }
    //@phpstan-ignore-next-line (php 8 you can return static instead of self)
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
                    $instance->name = static::parseName($value);

                    break;

                case 'issuer':
                    $instance->{$key} = $value instanceof Url ? $value : Url::fromValue($value);

                    break;

                case 'displayName':
                case 'clientSecret':
                case 'clientId':
                    $instance->{$key} = $value;

                    break;

                case 'enabled':
                    if (!\is_bool($value)) {
                        throw new InvalidArgumentException(\sprintf('%s must be a boolean', $key));
                    }
                    $instance->enabled = $value;

                break;

                case 'responseType':
                    $instance->responseType = $value instanceof OAuthResponseType ? $value : OAuthResponseType::fromProperties($value);

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
            'issuer' => $this->issuer ?? null,
            'displayName' => $this->displayName ?? null,
            'enabled' => $this->enabled ?? null,
            'clientId' => $this->clientId ?? null,
            'clientSecret' => $this->clientSecret ?? null,
            'responseType' => $this->responseType ?? null,
        ];
    }

    /**
     * @return array<String, bool|String|null>
     */
    public function jsonSerialize(): array
    {
        return \array_filter($this->toArray(), static fn ($value) => $value !== null);
    }

    /**
     * {@inheritDoc}
     */
    public static function validateName(string $name): bool
    {
        if (\stripos($name, 'oidc.') !== 0) {
            throw new InvalidArgumentException('name property is must start with "saml."');
        }

        return true;
    }

    public function __get(string $name)
    {
        if (!isset(self::FIELDS[$name])) {
            return trigger_error("Property $name doesn't exists and cannot be set.", E_USER_ERROR);
        }

        return $this->$name ?? null;
    }
}
