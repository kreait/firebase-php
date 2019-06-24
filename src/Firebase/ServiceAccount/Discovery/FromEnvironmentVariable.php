<?php

declare(strict_types=1);

namespace Kreait\Firebase\ServiceAccount\Discovery;

use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount;

/**
 * @internal
 */
class FromEnvironmentVariable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @internal
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @throws ServiceAccountDiscoveryFailed
     */
    public function __invoke(): ServiceAccount
    {
        $msg = \sprintf('%s: The environment variable "%s"', static::class, $this->name);

        if (!($path = $this->getValueFromEnvironment($this->name))) {
            throw new ServiceAccountDiscoveryFailed(\sprintf('%s is not set.', $msg));
        }

        $msg .= \sprintf(' points to "%s"', $path);

        try {
            return (new FromPath($path))();
        } catch (ServiceAccountDiscoveryFailed $e) {
            throw new ServiceAccountDiscoveryFailed(
                \sprintf('%s, but has errors: %s', $msg, $e->getMessage())
            );
        }
    }

    /**
     * @codeCoverageIgnore
     *
     * @return string|null
     */
    private function getValueFromEnvironment(string $name)
    {
        if ($value = \getenv($name, true)) {
            return (string) $value;
        }

        if ($value = \getenv($name, false)) {
            return (string) $value;
        }

        if ($value = $_ENV[$name] ?? null) {
            return (string) $value;
        }

        if ($value = $_SERVER[$name] ?? null) {
            return (string) $value;
        }

        return null;
    }
}
