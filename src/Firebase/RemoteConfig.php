<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Kreait\Firebase\Exception\RemoteConfig\ValidationFailed;
use Kreait\Firebase\Exception\RemoteConfig\VersionNotFound;
use Kreait\Firebase\Exception\RemoteConfigException;
use Kreait\Firebase\RemoteConfig\ApiClient;
use Kreait\Firebase\RemoteConfig\FindVersions;
use Kreait\Firebase\RemoteConfig\Template;
use Kreait\Firebase\RemoteConfig\Version;
use Kreait\Firebase\RemoteConfig\VersionNumber;
use Kreait\Firebase\Util\JSON;

/**
 * The Firebase Remote Config.
 *
 * @see https://firebase.google.com/docs/remote-config/use-config-rest
 * @see https://firebase.google.com/docs/remote-config/rest-reference
 */
class RemoteConfig
{
    /**
     * @var ApiClient
     */
    private $client;

    /**
     * @internal
     */
    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    public function get(): Template
    {
        return Template::fromResponse($this->client->getTemplate());
    }

    /**
     * Validates the given template without publishing it.
     *
     * @param Template|array $template
     *
     * @throws ValidationFailed if the validation failed
     */
    public function validate($template)
    {
        $template = $template instanceof Template ? $template : Template::fromArray($template);

        $this->client->validateTemplate($template);
    }

    /**
     * @param Template|array $template
     *
     * @throws RemoteConfigException
     *
     * @return string The etag value of the published template that can be compared to in later calls
     */
    public function publish($template): string
    {
        $template = $template instanceof Template ? $template : Template::fromArray($template);

        $etag = $this->client->publishTemplate($template)->getHeader('ETag');

        return \array_shift($etag) ?: '';
    }

    /**
     * Returns a version with the given number.
     *
     * @param VersionNumber|mixed $versionNumber
     *
     * @throws VersionNotFound
     */
    public function getVersion($versionNumber): Version
    {
        $versionNumber = $versionNumber instanceof VersionNumber
            ? $versionNumber
            : VersionNumber::fromValue($versionNumber);

        foreach ($this->listVersions() as $version) {
            if ($version->versionNumber()->equalsTo($versionNumber)) {
                return $version;
            }
        }

        throw VersionNotFound::withVersionNumber($versionNumber);
    }

    /**
     * Returns a version with the given number.
     *
     * @param VersionNumber|mixed $versionNumber
     *
     * @throws VersionNotFound
     */
    public function rollbackToVersion($versionNumber): Template
    {
        $versionNumber = $versionNumber instanceof VersionNumber
            ? $versionNumber
            : VersionNumber::fromValue($versionNumber);

        $response = $this->client->rollbackToVersion($versionNumber);

        return Template::fromResponse($response);
    }

    /**
     * @param FindVersions|array $query
     *
     * @return \Generator|Version[]
     */
    public function listVersions($query = null): \Generator
    {
        $query = $query instanceof FindVersions ? $query : FindVersions::fromArray((array) $query);
        $pageToken = null;
        $count = 0;
        $limit = $query->limit();

        do {
            $response = $this->client->listVersions($query, $pageToken);
            $result = JSON::decode((string) $response->getBody(), true);

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
}
