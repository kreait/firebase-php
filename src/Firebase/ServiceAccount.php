<?php

namespace Kreait\Firebase;

use Kreait\Firebase\Exception\InvalidArgumentException;
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

    public function withProjectId(string $value): ServiceAccount
    {
        $serviceAccount = clone $this;
        $serviceAccount->projectId = $value;

        return $serviceAccount;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function withClientId(string $value): ServiceAccount
    {
        $serviceAccount = clone $this;
        $serviceAccount->clientId = $value;

        return $serviceAccount;
    }

    public function getClientEmail(): string
    {
        return $this->clientEmail;
    }

    public function withClientEmail(string $value): ServiceAccount
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

    public function withPrivateKey(string $value): ServiceAccount
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
    public static function fromValue($value): ServiceAccount
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_string($value)) {
            try {
                return self::fromJson($value);
            } catch (InvalidArgumentException $e) {
                return self::fromJsonFile($value);
            }
        }

        if (is_array($value)) {
            return self::fromArray($value);
        }

        throw new InvalidArgumentException('Invalid service account specification.');
    }

    private static function fromArray(array $config): ServiceAccount
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

    private static function fromJson(string $json): ServiceAccount
    {
        $config = JSON::decode($json, true);

        return self::fromArray($config);
    }

    private static function fromJsonFile(string $filePath): ServiceAccount
    {
        if (!is_readable($filePath)) {
            throw new InvalidArgumentException(sprintf('%s is not readable.', $filePath));
        }

        return self::fromJson(file_get_contents($filePath));
    }
}
