<?php

namespace Kreait\Firebase\IdentityPlatform;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\IdentityPlatform\ParsesName;
use Kreait\Firebase\IdentityPlatform\OAuthResponseType;

class OAuthIdpConfig
{
    use ParsesName;
    private string $name;

    private ?string $issuer;
    private ?string $displayName;
    private ?bool $enabled;
    private ?string $clientId;
    private string $clientSecret;
    private ?OAuthResponseType $responseType;

    public const FIELD = ['name', 'displayName', 'enabled','clientId','clientSecret', 'responseType'];

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
    public static function withProperties(array $properties):
namespace Kreait\Firebase\IdentityPlatform;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\IdentityPlatform\ParsesName;
use Kreait\Firebase\IdentityPlatform\OAuthResponseType;

class OAuthIdpConfig
{
    use ParsesName;
    private string $name;

    private ?string $issuer;
    private ?string $displayName;
    private ?bool $enabled;
    private ?string $clientId;
    private string $clientSecret;
    private ?OAuthResponseType $responseType;

    public const FIELD = ['name', 'displayName', 'enabled','clientId','clientSecret', 'responseType'];

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
        $instance =  new static();

        if (!isset($properties['name'])) {
            throw new InvalidArgumentException('name property is a required string');
        }

        foreach ($properties as $key => $value) {
            switch ($key) {
                case 'name':
                    $instance->name = static::parseName($value);
                    break;
                case 'issuer':
                case 'displayName':
                case 'clientSecret':
                case 'clientId':
                    $instance->$key = $value;
                    break;
                case 'enabled':
                    if (!is_bool($value)) {
                        throw new InvalidArgumentException(sprintf('%s must be a boolean', $key));
                    }
                    $instance->enabled = $value;
                break;
                case 'responseType':
                    $instance->responseType = $value instanceof OAuthResponseType ? $value : OAuthResponseType::fromProperties($value);
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
            "name"         => $this->name,
            "issuer"       => $this->issuer ?? null,
            "displayName"  => $this->displayName ?? null,
            "enabled"      => $this->enabled ?? null,
            "clientId"     => $this->clientId ?? null,
            "clientSecret" => $this->clientSecret ?? null,
            "responseType" => $this->responseType ?? null,
        ];
    }

    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    public static function validateName(string $name): bool
    {
        if (stripos($name, 'oidc.') !== 0) {
            throw new InvalidArgumentException('name property is must start with "saml."');
        }
        return true;
    }
}

    {
        $instance =  new static();

        if (!isset($properties['name'])) {
            throw new InvalidArgumentException('name property is a required string');
        }

        foreach ($properties as $key => $value) {
            switch ($key) {
                case 'name':
                    $instance->name = static::parseName($value);
                    break;
                case 'issuer':
                case 'displayName':
                case 'clientSecret':
                case 'clientId':
                    $instance->$key = $value;
                    break;
                case 'enabled':
                    if (!is_bool($value)) {
                        throw new InvalidArgumentException(sprintf('%s must be a boolean', $key));
                    }
                    $instance->enabled = $value;
                break;
                case 'responseType':
                    $instance->responseType = $value instanceof OAuthResponseType ? $value : OAuthResponseType::fromProperties($value);
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
            "name"         => $this->name,
            "issuer"       => $this->issuer ?? null,
            "displayName"  => $this->displayName ?? null,
            "enabled"      => $this->enabled ?? null,
            "clientId"     => $this->clientId ?? null,
            "clientSecret" => $this->clientSecret ?? null,
            "responseType" => $this->responseType ?? null,
        ];
    }

    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    public static function validateName(string $name): bool
    {
        if (stripos($name, 'oidc.') !== 0) {
            throw new InvalidArgumentException('name property is must start with "saml."');
        }
        return true;
    }
}
