<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use Kreait\Firebase\Exception\RemoteConfig\ValidationFailed;
use Kreait\Firebase\Exception\RemoteConfig\VersionNotFound;
use Kreait\Firebase\Exception\RemoteConfigException;
use Kreait\Firebase\RemoteConfig\FindVersions;
use Kreait\Firebase\RemoteConfig\Template;
use Kreait\Firebase\RemoteConfig\Version;
use Kreait\Firebase\RemoteConfig\VersionNumber;
use Traversable;

/**
 * The Firebase Remote Config.
 *
 * @see https://firebase.google.com/docs/remote-config/use-config-rest
 * @see https://firebase.google.com/docs/reference/remote-config/rest
 *
 * @phpstan-import-type RemoteConfigTemplateShape from Template
 * @phpstan-import-type FindVersionsShape from FindVersions
 */
interface RemoteConfig
{
    /**
     * @param Version|VersionNumber|positive-int|non-empty-string $versionNumber
     *
     * @throws RemoteConfigException if something went wrong
     */
    public function get(Version|VersionNumber|int|string|null $versionNumber = null): Template;

    /**
     * Validates the given template without publishing it.
     *
     * @param Template|RemoteConfigTemplateShape $template
     *
     * @throws ValidationFailed if the validation failed
     * @throws RemoteConfigException
     */
    public function validate($template): void;

    /**
     * @param Template|RemoteConfigTemplateShape $template
     *
     * @throws RemoteConfigException
     *
     * @return non-empty-string The etag value of the published template that can be compared to in later calls
     */
    public function publish($template): string;

    /**
     * Returns a version with the given number.
     *
     * @param VersionNumber|positive-int|non-empty-string $versionNumber
     *
     * @throws VersionNotFound
     * @throws RemoteConfigException if something went wrong
     */
    public function getVersion(VersionNumber|int|string $versionNumber): Version;

    /**
     * Returns a version with the given number.
     *
     * @param VersionNumber|positive-int|non-empty-string $versionNumber
     *
     * @throws VersionNotFound
     * @throws RemoteConfigException if something went wrong
     */
    public function rollbackToVersion(VersionNumber|int|string $versionNumber): Template;

    /**
     * @param FindVersions|FindVersionsShape|null $query
     *
     * @throws RemoteConfigException if something went wrong
     *
     * @return Traversable<Version>
     */
    public function listVersions($query = null): Traversable;
}
