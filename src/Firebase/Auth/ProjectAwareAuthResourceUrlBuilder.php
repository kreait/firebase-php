<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Util;

/**
 * @internal
 */
final class ProjectAwareAuthResourceUrlBuilder
{
    private const URL_FORMAT = 'https://identitytoolkit.googleapis.com/{version}/projects/{projectId}{api}';
    private const EMULATOR_URL_FORMAT = 'http://{host}/identitytoolkit.googleapis.com/{version}/projects/{projectId}{api}';

    private const DEFAULT_API_VERSION = 'v1';

    private string $projectId;
    private string $apiVersion;
    private string $urlFormat;

    private function __construct(string $projectId, string $apiVersion, string $urlFormat)
    {
        $this->projectId = $projectId;
        $this->apiVersion = $apiVersion;
        $this->urlFormat = $urlFormat;
    }

    public static function forProject(string $projectId, ?string $version = null): self
    {
        $version = $version ?? self::DEFAULT_API_VERSION;
        $emulatorHost = Util::authEmulatorHost();

        $urlFormat = $emulatorHost !== ''
            ? \str_replace('{host}', $emulatorHost, self::EMULATOR_URL_FORMAT)
            : self::URL_FORMAT;

        return new self($projectId, $version, $urlFormat);
    }

    /**
     * @param array<string, scalar>|null $params
     */
    public function getUrl(?string $api = null, ?array $params = null): string
    {
        $api = $api ?? '';

        $url = \strtr($this->urlFormat, [
            '{version}' => $this->apiVersion,
            '{projectId}' => $this->projectId,
            '{api}' => $api,
        ]);

        if ($params !== null) {
            $url .= '?'.\http_build_query($params);
        }

        return $url;
    }
}
