<?php

namespace Kreait\Firebase\IdentityPlatform;

use Kreait\Firebase\Exception\InvalidArgumentException;

class OauthResponseType implements \JsonSerializable
{
    private ?bool $idToken;
    private ?bool $code;

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new static();
    }

    public static function fromProperties(array $properties)
    {
        $instance = new static();

        foreach ($properties as $key => $value) {
            switch ($key) {
                case 'idToken':
                case 'code':
                    if (!is_bool($value)) {
                        throw new InvalidArgumentException(sprintf('%s must be a boolean', $key));
                    }
                    $instance->$key = $value;

                    break;
                default:
                    throw new InvalidArgumentException(sprintf('%s is not a valid property', $key));
            }
        }
        $idTokenEnabled = $instance->idToken ?? null;
        $codeEnabled = $instance->code ?? null;
        if ($idTokenEnabled && $codeEnabled) {
            throw new InvalidArgumentException('{code: true, idToken: true} is not yet supported');
        }
    }

    public function toArray()
    {
        return [
            'idToken' => $this->idToken ?? null,
            'code'    => $this->code ?? null,
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
