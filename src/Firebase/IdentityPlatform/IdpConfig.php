<?php
namespace Kreait\Firebase\IdentityPlatform;

use InvalidArgumentException;
use Kreait\Firebase\Value\Url;
use Kreait\Firebase\Value\Certificate;

class IdpConfig implements \JsonSerializable
{
    private string $idpEntityId;
    private Url $ssoUrl;
    /**
     * @var array<Certificate>
     */
    private array $idpCertificates;
    private bool $signRequest;

    public const FIELDS = ['idpEntityId', 'ssoUrl', 'idpCertificates', 'signRequest'];

    private function __construct()
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
    public static function withProperties(array $properties): self
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
                    if (!is_array($value)) {
                        throw new InvalidArgumentException(sprintf('%s must be an array', $key));
                    }
                    $instance->idpCertificates = array_map(fn ($certificate) => $certificate instanceof Certificate ? $certificate : ['x509Certificate' => new Certificate($certificate['x509Certificate'])], $value);
                    break;
                case 'signRequest':
                    if (!is_bool($value)) {
                        throw new InvalidArgumentException(sprintf('%s is not a valid property', $key));
                    }
                    $instance->signRequest = $value;
                    break;
                default:
                 throw new InvalidArgumentException(sprintf('%s is not a valid property', $key));
            }
        }

        return $instance;
    }

    public function toArray() : array
    {
        return [
            'idpEntityId' => $this->idpEntityId,
            'ssoUrl' => $this->ssoUrl,
            'idpCertificates' => $this->idpCertificates,
            'signRequest' => $this->signRequest,
        ];
    }

    public function jsonSerialize() : array
    {
        return $this->toArray();
    }
}
