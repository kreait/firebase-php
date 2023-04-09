<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Beste\Json;
use Kreait\Firebase\Exception\RemoteConfig\VersionNotFound;
use Kreait\Firebase\RemoteConfig\ApiClient;
use Kreait\Firebase\RemoteConfig\FindVersions;
use Kreait\Firebase\RemoteConfig\Template;
use Kreait\Firebase\RemoteConfig\Version;
use Kreait\Firebase\RemoteConfig\VersionNumber;
use Psr\Http\Message\ResponseInterface;
use Traversable;

use function array_shift;
use function is_string;

/**
 * @internal
 *
 * @phpstan-import-type RemoteConfigTemplateShape from Template
 */
final class RemoteConfig implements Contract\RemoteConfig
{
    public function __construct(private readonly ApiClient $client)
    {
    }

    public function get(): Template
    {
        return $this->buildTemplateFromResponse($this->client->getTemplate());
    }

    public function validate($template): void
    {
        $this->client->validateTemplate($this->ensureTemplate($template));
    }

    public function publish($template): string
    {
        $etag = $this->client
            ->publishTemplate($this->ensureTemplate($template))
            ->getHeader('ETag')
        ;

        $etag = array_shift($etag);

        if (!is_string($etag)) {
            return '*';
        }

        if ($etag === '') {
            return '*';
        }

        return $etag;
    }

    public function getVersion(VersionNumber|int|string $versionNumber): Version
    {
        $versionNumber = $this->ensureVersionNumber($versionNumber);

        foreach ($this->listVersions() as $version) {
            if ($version->versionNumber()->equalsTo($versionNumber)) {
                return $version;
            }
        }

        throw VersionNotFound::withVersionNumber($versionNumber);
    }

    public function rollbackToVersion(VersionNumber|int|string $versionNumber): Template
    {
        $versionNumber = $this->ensureVersionNumber($versionNumber);

        return $this->buildTemplateFromResponse($this->client->rollbackToVersion($versionNumber));
    }

    public function listVersions($query = null): Traversable
    {
        $query = $query instanceof FindVersions ? $query : FindVersions::fromArray((array) $query);
        $pageToken = null;
        $count = 0;
        $limit = $query->limit();

        do {
            $response = $this->client->listVersions($query, $pageToken);
            $result = Json::decode((string) $response->getBody(), true);

            foreach ((array) ($result['versions'] ?? []) as $versionData) {
                ++$count;

                yield Version::fromArray($versionData);

                if ($count === $limit) {
                    return;
                }
            }

            $pageToken = $result['nextPageToken'] ?? null;
        } while ($pageToken);
    }

    /**
     * @param Template|RemoteConfigTemplateShape $value
     */
    private function ensureTemplate($value): Template
    {
        return $value instanceof Template ? $value : Template::fromArray($value);
    }

    /**
     * @param VersionNumber|positive-int|non-empty-string $value
     */
    private function ensureVersionNumber(VersionNumber|int|string $value): VersionNumber
    {
        return $value instanceof VersionNumber ? $value : VersionNumber::fromValue($value);
    }

    private function buildTemplateFromResponse(ResponseInterface $response): Template
    {
        $etagHeader = $response->getHeader('ETag');
        $etag = array_shift($etagHeader) ?: '*';

        $data = Json::decode((string) $response->getBody(), true);

        return Template::fromArray($data, $etag);
    }
}
