<?php

declare(strict_types=1);

namespace Kreait\Firebase\ServiceAccount\Discovery;

use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount;
use Throwable;

/**
 * @internal
 *
 * @deprecated 4.42
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

        if (!($value = $this->getValueFromEnvironment($this->name))) {
            throw new ServiceAccountDiscoveryFailed(\sprintf('%s is not set.', $msg));
        }

        if (\mb_strpos($value, '{') === 0) {
            $msg .= 'is a JSON string';
        } else {
            $msg .= \sprintf(' points to "%s"', $value);
        }

        try {
            return ServiceAccount::fromValue($value);
        } catch (Throwable $e) {
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
