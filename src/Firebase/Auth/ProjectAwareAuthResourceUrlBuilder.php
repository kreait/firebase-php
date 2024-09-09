<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Util;

use function http_build_query;
use function str_replace;
use function strtr;

/**
 * @internal
 */
final class ProjectAwareAuthResourceUrlBuilder
{
    private const URL_FORMAT = 'https://identitytoolkit.googleapis.com/{version}/projects/{projectId}{api}';
    private const EMULATOR_URL_FORMAT = 'http://{host}/identitytoolkit.googleapis.com/{version}/projects/{projectId}{api}';
    private const DEFAULT_API_VERSION = 'v1';

    /**
     * @param non-empty-string $projectId
     * @param non-empty-string $apiVersion
     * @param non-empty-string $urlFormat
     */
    private function __construct(
        private readonly string $projectId,
        private readonly string $apiVersion,
        private readonly string $urlFormat,
    ) {
    }

    /**
     * @param non-empty-string $projectId
     * @param non-empty-string|null $version
     */
    public static function forProject(string $projectId, ?string $version = null): self
    {
        $version ??= self::DEFAULT_API_VERSION;
        $emulatorHost = Util::authEmulatorHost();

        $urlFormat = $emulatorHost !== null
            ? str_replace('{host}', $emulatorHost, self::EMULATOR_URL_FORMAT)
            : self::URL_FORMAT;

        return new self($projectId, $version, $urlFormat);
    }

    /**
     * @param non-empty-string|null $api
     * @param array<non-empty-string, scalar>|null $params
     */
    public function getUrl(?string $api = null, ?array $params = null): string
    {
        $api ??= '';

        $url = strtr($this->urlFormat, [
            '{version}' => $this->apiVersion,
            '{projectId}' => $this->projectId,
            '{api}' => $api,
        ]);

        if ($params !== null) {
            $url .= '?'.http_build_query($params);
        }

        return $url;
    }
}
