<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\ServiceAccount\Discoverer;
use Kreait\Firebase\Util\JSON;

class ServiceAccount
{
    private $projectId;
    private $sanitizedProjectId;
    private $clientId;
    private $clientEmail;
    private $privateKey;

    public function getProjectId(): string
    {
        return $this->projectId;
    }

    public function getSanitizedProjectId(): string
    {
        if (!$this->sanitizedProjectId) {
            $this->sanitizedProjectId = \preg_replace('/[^A-Za-z0-9\-]/', '-', $this->projectId);
        }

        return $this->sanitizedProjectId;
    }

    public function withProjectId(string $value): self
    {
        $serviceAccount = clone $this;
        $serviceAccount->projectId = $value;
        $serviceAccount->sanitizedProjectId = null;

        return $serviceAccount;
    }

    public function hasClientId(): bool
    {
        return (bool) $this->clientId;
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
        if (!\filter_var($value, \FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(\sprintf('"%s" is not a valid email.', $value));
        }
        $serviceAccount = clone $this;
        $serviceAccount->clientEmail = $value;

        return $serviceAccount;
    }

    public function hasPrivateKey(): bool
    {
        return (bool) $this->privateKey;
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
        $requiredFields = ['project_id', 'client_id', 'client_email', 'private_key'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($config[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new InvalidArgumentException(
                'The following fields are missing/empty in the Service Account specification: "'
                .\implode('", "', $missingFields)
                .'". Please make sure you download the Service Account JSON file from the Service Accounts tab '
                .'in the Firebase Console, as shown in the documentation on '
                .'https://firebase.google.com/docs/admin/setup#add_firebase_to_your_app'
            );
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
        try {
            $file = new \SplFileObject($filePath);
            $json = $file->fread($file->getSize());
        } catch (\Throwable $e) {
            throw new InvalidArgumentException(\sprintf('%s can not be read: %s', $filePath, $e->getMessage()));
        }

        try {
            return self::fromJson($json);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException(\sprintf('%s could not be parsed to a Service Account: %s', $filePath, $e->getMessage()));
        }
    }

    public static function withProjectIdAndServiceAccountId(string $projectId, string $serviceAccountId): self
    {
        $serviceAccount = new self();
        $serviceAccount->projectId = $projectId;
        $serviceAccount->clientEmail = $serviceAccountId;

        return $serviceAccount;
    }

    /**
     * @return ServiceAccount
     */
    public static function discover(Discoverer $discoverer = null): self
    {
        $discoverer = $discoverer ?: new Discoverer();

        return $discoverer->discover();
    }
}
