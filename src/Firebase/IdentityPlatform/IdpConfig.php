<?php

declare(strict_types=1);

namespace Kreait\Firebase\IdentityPlatform;

use InvalidArgumentException;
use Kreait\Firebase\Value\Certificate;
use Kreait\Firebase\Value\Url;

class IdpConfig implements \JsonSerializable
{
    // @phpstan-ignore-next-line
    private string $idpEntityId;
    // @phpstan-ignore-next-line
    private Url $ssoUrl;
    /**
     * @var array<int, array<String,Certificate>>
     * @phpstan-ignore-next-line
     */
    private array $idpCertificates;
    // @phpstan-ignore-next-line
    private bool $signRequest;

    public const FIELDS = ['idpEntityId', 'ssoUrl', 'idpCertificates', 'signRequest'];

    final private function __construct()
    {
    }

    // @phpstan-ignore-next-line (php 8 you can return static instead of self)
    public static function new()
    {
        return new static();
    }

    /**
     * @param array<String, mixed> $properties
     *
     * @throws InvalidArgumentException when invalid properties have been provided
     * @phpstan-ignore-next-line (php 8 you can return static instead of self)
     */
    public static function withProperties(array $properties)
    {
        $instance = new static();

        foreach ($properties as $key => $value) {
            switch ($key) {
                case 'idpEntityId':
                    $instance->idpEntityId = $value;

                    break;

                case 'ssoUrl':
                    $instance->ssoUrl = $value instanceof Url ? $value : Url::fromValue($value);

                    break;

                case 'idpCertificates':
                    if (!\is_array($value)) {
                        throw new InvalidArgumentException(\sprintf('%s must be an array', $key));
                    }

                    $instance->idpCertificates = \array_map(
                        /**
                         * @param Certificate|array<String,String> $certificate
                         *
                         * @return array<Certificate>
                         */
                        function ($certificate) {
                            $certificateObject = $certificate instanceof Certificate ? $certificate : new Certificate($certificate);

                            return ['x509Certificate' => $certificateObject];
                        },
                        $value
                    );

                    break;

                case 'signRequest':
                    if (!\is_bool($value)) {
                        throw new InvalidArgumentException(\sprintf('%s is not a valid property', $key));
                    }
                    $instance->signRequest = $value;

                    break;

                default:
                 throw new InvalidArgumentException(\sprintf('%s is not a valid property', $key));
            }
        }

        return $instance;
    }

    /**
     * To Array
     *
     * @return array<String, mixed>
     */
    public function toArray(): array
    {
        return [
            'idpEntityId' => $this->idpEntityId,
            'ssoUrl' => $this->ssoUrl,
            'idpCertificates' => $this->idpCertificates,
            'signRequest' => $this->signRequest,
        ];
    }

    /**
     * @return array<String,String>
     */
    public function jsonSerialize(): array
    {
        return \array_filter($this->toArray(), static fn ($value) => $value !== null);
    }

    public function __get(string $name)
    {
        if (array_search($name,self::FIELDS) === false) {
            return trigger_error("Property $name doesn't exists and cannot be set.", E_USER_ERROR);
        }

        return $this->$name ?? null;
    }
}
