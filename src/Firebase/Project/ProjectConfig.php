<?php

declare(strict_types=1);

namespace Kreait\Firebase\Project;

final class ProjectConfig implements Config
{
    private const ENV_PREFIX = 'FIREBASE_';
    private const ENV_DATABASE_URL = 'DATABASE_URL';
    private const ENV_DYNAMIC_LINKS_DOMAIN = 'DYNAMIC_LINKS_DOMAIN';
    private const ENV_SERVICE_ACCOUNT = 'CREDENTIALS';

    /** @var string|null */
    private $defaultDatabaseUrl;

    /** @var string|null */
    private $defaultDynamicLinksDomain;

    /** @var string|null */
    private $serviceAccount;

    private function __construct()
    {
    }

    public static function new(): self
    {
        return new self();
    }

    public static function fromEnvironment(?string $prefix = null): self
    {
        $prefix = $prefix ?? self::ENV_PREFIX;

        $config = new self();

        if ($serviceAccount = self::getenv($prefix, self::ENV_SERVICE_ACCOUNT)) {
            $config->serviceAccount = $serviceAccount;
        }

        if ($databaseUrl = self::getenv($prefix, self::ENV_DATABASE_URL)) {
            $config->defaultDatabaseUrl = $databaseUrl;
        }

        if ($dynamicLinksDomain = self::getenv($prefix, self::ENV_DYNAMIC_LINKS_DOMAIN)) {
            $config->defaultDynamicLinksDomain = $dynamicLinksDomain;
        }

        return $config;
    }

    public function defaultDatabaseUrl(): ?string
    {
        return $this->defaultDatabaseUrl;
    }

    public function withDefaultDatabaseUrl(string $defaultDatabaseUrl): self
    {
        $config = clone $this;
        $config->defaultDatabaseUrl = $defaultDatabaseUrl;

        return $config;
    }

    public function defaultDynamicLinksDomain(): ?string
    {
        return $this->defaultDynamicLinksDomain;
    }

    public function withDefaultDynamicLinksDomain(string $defaultDynamicLinksDomain): self
    {
        $config = clone $this;
        $config->defaultDynamicLinksDomain = $defaultDynamicLinksDomain;

        return $config;
    }

    public function serviceAccount(): ?string
    {
        return $this->serviceAccount;
    }

    public function withServiceAccount(string $serviceAccount): self
    {
        $config = clone $this;
        $config->serviceAccount = $serviceAccount;

        return $config;
    }

    private static function getenv(string $prefix, string $name): ?string
    {
        $fullName = $prefix.$name;

        $value = $_SERVER[$fullName] ?? $_ENV[$fullName] ?? \getenv($fullName);

        if ($value !== false && $value !== null) {
            return (string) $value;
        }

        return null;
    }
}
