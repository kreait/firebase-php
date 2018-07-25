<?php

namespace Kreait\Firebase;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\ServiceAccount\Discoverer;
use Kreait\Firebase\Util\JSON;

class ServiceAccount
{
    private $projectId;
    private $clientId;
    private $clientEmail;
    private $privateKey;

    public function getProjectId(): string
    {
        return $this->projectId;
    }

    public function withProjectId(string $value): self
    {
        $value = preg_replace('/[^A-Za-z0-9\-]/', '-', $value);

        $serviceAccount = clone $this;
        $serviceAccount->projectId = $value;

        return $serviceAccount;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function withClientId(string $value): self
    {
        $serviceAccount = clone $this;
        $serviceAccount->clientId = $value;

        return $serviceAccount;
    }

    public function getClientEmail(): string
    {
        return $this->clientEmail;
    }

    public function withClientEmail(string $value): self
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid email.', $value));
        }
        $serviceAccount = clone $this;
        $serviceAccount->clientEmail = $value;

        return $serviceAccount;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    public function withPrivateKey(string $value): self
    {
        $serviceAccount = clone $this;
        $serviceAccount->privateKey = $value;

        return $serviceAccount;
    }

    /**
     * @param mixed $value
     *
     * @throws InvalidArgumentException
     *
     * @return ServiceAccount
     */
    public static function fromValue($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (\is_string($value)) {
            try {
                return self::fromJson($value);
            } catch (InvalidArgumentException $e) {
                return self::fromJsonFile($value);
            }
        }

        if (\is_array($value)) {
            return self::fromArray($value);
        }

        throw new InvalidArgumentException('Invalid service account specification.');
    }

    public static function fromArray(array $config): self
    {
        if (!isset($config['project_id'], $config['client_id'], $config['client_email'], $config['private_key'])) {
            throw new InvalidArgumentException('Missing/empty values in Service Account Config.');
        }

        return (new self())
            ->withProjectId($config['project_id'])
            ->withClientId($config['client_id'])
            ->withClientEmail($config['client_email'])
            ->withPrivateKey($config['private_key']);
    }

    public static function fromJson(string $json): self
    {
        $config = JSON::decode($json, true);

        return self::fromArray($config);
    }

    public static function fromJsonFile(string $filePath): self
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException(sprintf('%s does not exist.', $filePath));
        }

        if (is_link($filePath)) {
            $filePath = (string) realpath($filePath);
        }

        if (!is_file($filePath)) {
            throw new InvalidArgumentException(sprintf('%s is not a file.', $filePath));
        }

        if (!is_readable($filePath)) {
            throw new InvalidArgumentException(sprintf('%s is not readable.', $filePath));
        }

        $jsonString = file_get_contents($filePath);

        return self::fromJson($jsonString);
    }

    /**
     * @param Discoverer|null $discoverer
     *
     * @return ServiceAccount
     */
    public static function discover(Discoverer $discoverer = null): self
    {
        $discoverer = $discoverer ?: new Discoverer();

        return $discoverer->discover();
    }
}
