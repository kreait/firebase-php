<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

use function array_key_exists;

/**
 * @phpstan-import-type RemoteConfigPersonalizationValueShape from PersonalizationValue
 * @phpstan-import-type RemoteConfigExplicitValueShape from ExplicitValue
 *
 * @phpstan-type RemoteConfigInAppDefaultValueShape array{
 *     useInAppDefault: bool
 * }
 */
class DefaultValue implements JsonSerializable
{
    /** @deprecated 6.9.0 */
    public const IN_APP_DEFAULT_VALUE = true;

    /**
     * @var RemoteConfigExplicitValueShape|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape
     */
    private $data;

    /**
     * @param RemoteConfigExplicitValueShape|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape $data
     */
    private function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @deprecated 6.9.0 Use {@see useInAppDefault()} instead
     */
    public static function none(): self
    {
        return self::useInAppDefault();
    }

    public static function useInAppDefault(): self
    {
        return new self(['useInAppDefault' => true]);
    }

    public static function with(string $value): self
    {
        return new self(['value' => $value]);
    }

    /**
     * @deprecated 6.9.0 Use {@see toArray()} instead
     *
     * @return string|bool|null
     */
    public function value()
    {
        if (array_key_exists('value', $this->data)) {
            return $this->data['value'];
        }

        if (array_key_exists('useInAppDefault', $this->data)) {
            return $this->data['useInAppDefault'];
        }

        if (array_key_exists('personalizationId', $this->data)) {
            return $this->data['personalizationId'];
        }

        return null;
    }

    /**
     * @return RemoteConfigExplicitValueShape|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @param RemoteConfigExplicitValueShape|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * @return RemoteConfigExplicitValueShape|RemoteConfigInAppDefaultValueShape|RemoteConfigPersonalizationValueShape
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
