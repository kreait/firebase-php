<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Util\JSON;
use Throwable;

/**
 * @internal
 */
class ServiceAccount
{
    /**
     * @var array{
     *     project_id?: string,
     *     client_email?: string,
     *     private_key?: string,
     *     type: string
     * }
     */
    private array $data;

    /**
     * @phpstan-param array{
     *     project_id?: string,
     *     client_email?: string,
     *     private_key?: string,
     *     type: string
     * } $data
     */
    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getProjectId(): string
    {
        return $this->data['project_id'] ?? '';
    }

    public function getClientEmail(): string
    {
        return $this->data['client_email'] ?? '';
    }

    public function getPrivateKey(): string
    {
        return $this->data['private_key'] ?? '';
    }

    /**
     * @return array{
     *     project_id?: string,
     *     client_email?: string,
     *     private_key?: string,
     *     type: string
     * }
     */
    public function asArray(): array
    {
        return $this->data;
    }

    /**
     * @param self|string|array|mixed $value
     *
     * @throws InvalidArgumentException
     */
    public static function fromValue($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (\is_string($value)) {
            try {
                if (\str_starts_with($value, '{')) {
                    return self::fromJson($value);
                }

                return self::fromJsonFile($value);
            } catch (Throwable $e) {
                throw new InvalidArgumentException('Invalid service account: '.$e->getMessage(), $e->getCode(), $e);
            }
        }

        if (\is_array($value)) {
            try {
                return self::fromArray($value);
            } catch (Throwable $e) {
                throw new InvalidArgumentException('Invalid service account: '.$e->getMessage(), $e->getCode(), $e);
            }
        }

        throw new InvalidArgumentException('Invalid service account: Unsupported value');
    }

    /**
     * @param array<string, string> $data
     */
    private static function fromArray(array $data): self
    {
        if (!\array_key_exists('type', $data) || $data['type'] !== 'service_account') {
            throw new InvalidArgumentException(
                'A Service Account specification must have a field "type" with "service_account" as its value.'
                .' Please make sure you download the Service Account JSON file from the Service Accounts tab'
                .' in the Firebase Console, as shown in the documentation on'
                .' https://firebase.google.com/docs/admin/setup#add_firebase_to_your_app'
            );
        }

        return new self($data);
    }

    private static function fromJson(string $json): self
    {
        $config = JSON::decode($json, true);

        return self::fromArray($config);
    }

    private static function fromJsonFile(string $filePath): self
    {
        try {
            $file = new \SplFileObject($filePath);
            $json = (string) $file->fread($file->getSize());
        } catch (Throwable $e) {
            throw new InvalidArgumentException("{$filePath} can not be read: {$e->getMessage()}");
        }

        try {
            $serviceAccount = self::fromJson($json);
        } catch (Throwable $e) {
            throw new InvalidArgumentException(\sprintf('%s could not be parsed to a Service Account: %s', $filePath, $e->getMessage()));
        }

        return $serviceAccount;
    }
}
