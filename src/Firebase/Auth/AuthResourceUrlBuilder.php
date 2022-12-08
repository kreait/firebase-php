<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Util;

use function assert;
use function http_build_query;
use function str_replace;
use function strtr;

/**
 * @internal
 */
final class AuthResourceUrlBuilder
{
    private const URL_FORMAT = 'https://identitytoolkit.googleapis.com/{version}{api}';
    private const EMULATOR_URL_FORMAT = 'http://{host}/identitytoolkit.googleapis.com/{version}{api}';
    private const DEFAULT_API_VERSION = 'v1';

    /**
     * @param non-empty-string $apiVersion
     * @param non-empty-string $urlFormat
     */
    private function __construct(
        private readonly string $apiVersion,
        private readonly string $urlFormat,
    ) {
    }

    /**
     * @param non-empty-string|null $version
     */
    public static function create(?string $version = null): self
    {
        $version ??= self::DEFAULT_API_VERSION;
        $emulatorHost = Util::authEmulatorHost();

        $urlFormat = $emulatorHost !== null
            ? str_replace('{host}', $emulatorHost, self::EMULATOR_URL_FORMAT)
            : self::URL_FORMAT;

        return new self($version, $urlFormat);
    }

    /**
     * @param non-empty-string|null $api
     * @param array<non-empty-string, scalar>|null $params
     *
     * @return non-empty-string
     */
    public function getUrl(?string $api = null, ?array $params = null): string
    {
        $api ??= '';

        $url = strtr($this->urlFormat, [
            '{version}' => $this->apiVersion,
            '{api}' => $api,
        ]);
        assert($url !== '');

        if ($params !== null) {
            $url .= '?'.http_build_query($params);
        }

        return $url;
    }
}
