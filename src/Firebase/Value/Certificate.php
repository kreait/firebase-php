<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;

class Certificate implements \JsonSerializable
{
    private string $value;

    private ?array $certificate;

    public function __construct(string $certificate)
    {
        $parsedCertificate = openssl_x509_parse($certificate);

        if ($parsedCertificate === false) {
            throw new InvalidArgumentException('Invalid X.509 Certificate');
        }
        $this->value = $certificate;
        $this->certificate = $parsedCertificate;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

    public function details() : array
    {
        return $this->certificate;
    }

    /**
     * @param self|string $other
     */
    public function equalsTo($other): bool
    {
        return $this->value === (string) $other;
    }
}
