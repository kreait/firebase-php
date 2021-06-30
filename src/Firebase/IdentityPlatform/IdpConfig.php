<?php
namespace Kreait\Firebase\IdentityPlatform;

use InvalidArgumentException;
use Kreait\Firebase\Value\Url;
use Kreait\Firebase\Value\Certificate;

class IdpConfig implements \JsonSerializable
{
    // @phpstan-ignore-next-line
    private string $idpEntityId;
    // @phpstan-ignore-next-line
    private Url $ssoUrl;
    /**
     * @var array<int, array<String,Certificate>> $idpCertificates
     *  @phpstan-ignore-next-line
     */
    private array $idpCertificates;
    // @phpstan-ignore-next-line
    private bool $signRequest;

    public const FIELDS = ['idpEntityId', 'ssoUrl', 'idpCertificates', 'signRequest'];

    final private function __construct()
    {
    }

    public static function new(): static
    {
        return new static();
    }

    /**
     * @param array<String, mixed> $properties
     *
     * @throws InvalidArgumentException when invalid properties have been provided
     */
    public static function withProperties(array $properties): static
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

                    $instance->idpCertificates = array_map(
                        /**
                         * @param Certificate|array<String,String> $certificate
                         * @return array<Certificate>
                         */
                        function ($certificate) {
                            $certificateObject = $certificate instanceof Certificate ? $certificate : new Certificate($certificate['x509Certificate']);
                            return ['x509Certificate' => $certificateObject];
                        },
                        $value
                    );
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
    /**
     * Undocumented function
     *
     * @return array<String, mixed>
     */
    public function toArray() : array
    {
        return [
            'idpEntityId' => $this->idpEntityId,
            'ssoUrl' => $this->ssoUrl,
            'idpCertificates' => $this->idpCertificates,
            'signRequest' => $this->signRequest,
        ];
    }
    /**
     *
     *
     * @return array<String,String>
     */
    public function jsonSerialize() : array
    {
        return \array_filter($this->toArray(), static fn ($value) => $value !== null);
    }
}
