<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

class DefaultValue implements \JsonSerializable
{
    public const IN_APP_DEFAULT_VALUE = true;

    /** @var string|bool */
    private $value;

    /**
     * @param string|bool $value
     */
    private function __construct($value)
    {
        $this->value = \is_string($value) ? $value : true;
    }

    public static function none(): self
    {
        return new self(self::IN_APP_DEFAULT_VALUE);
    }

    public static function with(string $value): self
    {
        return new self($value);
    }

    /**
     * @return string|bool
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @param array<string, string|null> $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['value'] ?? $data['useInAppDefault'] ?? true);
    }

    /**
     * @return array<string, string|bool>
     */
    public function jsonSerialize(): array
    {
        $key = $this->value === true ? 'useInAppDefault' : 'value';

        return [$key => $this->value];
    }
}
