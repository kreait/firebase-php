<?php

namespace Firebase;

use Firebase\Exception\InvalidArgumentException;
use Firebase\Util\JSON;

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

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getClientEmail(): string
    {
        return $this->clientEmail;
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->privateKey;
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
        if ($value instanceof ServiceAccount) {
            return $value;
        }

        if (is_string($value)) {
            return self::fromJsonFile($value);
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

        $account = new self();
        $account->projectId = $config['project_id'];
        $account->clientId = $config['client_id'];
        $account->clientEmail = $config['client_email'];
        $account->privateKey = $config['private_key'];

        return $account;
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
