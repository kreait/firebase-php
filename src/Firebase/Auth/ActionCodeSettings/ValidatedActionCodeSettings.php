<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth\ActionCodeSettings;

use GuzzleHttp\Psr7\Utils;
use Kreait\Firebase\Auth\ActionCodeSettings;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Psr\Http\Message\UriInterface;

use function array_filter;
use function is_bool;
use function is_string;
use function mb_strtolower;

final class ValidatedActionCodeSettings implements ActionCodeSettings
{
    private ?UriInterface $continueUrl = null;
    private ?bool $canHandleCodeInApp = null;
    private ?UriInterface $dynamicLinkDomain = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $androidPackageName = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $androidMinimumVersion = null;
    private ?bool $androidInstallApp = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $iOSBundleId = null;

    private function __construct()
    {
    }

    public static function empty(): self
    {
        return new self();
    }

    /**
     * @param array<non-empty-string, mixed> $settings
     */
    public static function fromArray(array $settings): self
    {
        $instance = new self();

        $settings = array_filter($settings, static fn($value): bool => $value !== null);

        foreach ($settings as $key => $value) {
            switch (mb_strtolower($key)) {
                case 'continueurl':
                case 'url':
                    $instance->continueUrl = Utils::uriFor($value);

                    break;

                case 'handlecodeinapp':
                    $instance->canHandleCodeInApp = (bool) $value;

                    break;

                case 'dynamiclinkdomain':
                    $instance->dynamicLinkDomain = Utils::uriFor($value);

                    break;

                case 'androidpackagename':
                    $instance->androidPackageName = $value;

                    break;

                case 'androidminimumversion':
                    $instance->androidMinimumVersion = $value;

                    break;

                case 'androidinstallapp':
                    $instance->androidInstallApp = (bool) $value;

                    break;

                case 'iosbundleid':
                    $instance->iOSBundleId = $value;

                    break;

                default:
                    throw new InvalidArgumentException("Unsupported action code setting '{$key}'");
            }
        }

        return $instance;
    }

    /**
     * @return array<non-empty-string, bool|non-empty-string>
     */
    public function toArray(): array
    {
        $continueUrl = $this->continueUrl !== null ? (string) $this->continueUrl : null;
        $dynamicLinkDomain = $this->dynamicLinkDomain !== null ? (string) $this->dynamicLinkDomain : null;

        return array_filter([
            'continueUrl' => $continueUrl,
            'canHandleCodeInApp' => $this->canHandleCodeInApp,
            'dynamicLinkDomain' => $dynamicLinkDomain,
            'androidPackageName' => $this->androidPackageName,
            'androidMinimumVersion' => $this->androidMinimumVersion,
            'androidInstallApp' => $this->androidInstallApp,
            'iOSBundleId' => $this->iOSBundleId,
        ], static fn($value): bool => is_bool($value) || (is_string($value) && $value !== ''));
    }
}
