<?php

declare(strict_types=1);

namespace Kreait;

use Kreait\Firebase\Exception\FirebaseError;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Project;
use Kreait\Firebase\Project\Config;
use Kreait\Firebase\Project\ProjectConfig;

final class Firebase
{
    private const DEFAULT_NAME = '[DEFAULT]';

    /** @var array<string, Project> */
    private $projects = [];

    /**
     * @throws FirebaseError if a project already exists
     */
    public function initializeProject(?Config $config = null, ?string $name = null): Project
    {
        $name = $name ?? self::DEFAULT_NAME;

        self::assertNullOrNonEmptyProjectName($name);

        if (\array_key_exists($name, $this->projects)) {
            if ($name === self::DEFAULT_NAME) {
                throw new FirebaseError(
                    'The default Firebase project already exists. This means you called initializeProject()'
                    .' more than once without providing a project name as the second argument. In most cases'
                    .' you only need to call `initializeProject()` once. But if you want to initialize'
                    .' multiple projects, pass a second argument to initializeProject()'
                    .' to give each project a unique name.'
                );
            }

            throw new FirebaseError(
                'A Firebase project named "'.$name.'" already exists. This means you called initializeProject()'
                .' more than once with the same project name as the second argument. Make sure you provide a'
                .' unique name every time you call initializeProject().'
            );
        }

        $config = $config ?? ProjectConfig::fromEnvironment();

        $this->projects[$name] = new Project($config, new Factory());

        return $this->projects[$name];
    }

    /**
     * @throws FirebaseError if a project doesn't exist
     */
    public function project(?string $name = null): Project
    {
        $name = $name ?? self::DEFAULT_NAME;

        self::assertNullOrNonEmptyProjectName($name);

        if (!\array_key_exists($name, $this->projects)) {
            $message = ($name === self::DEFAULT_NAME)
                ? 'The default Firebase project does not exist.'
                : 'A Firebase project named "'.$name.'" does not exist.';

            throw new FirebaseError(
                $message.' Make sure that you call initializeProject() before using'
                .' any of the Firebase services.');
        }

        return $this->projects[$name];
    }

    /**
     * @throws FirebaseError
     */
    private static function assertNullOrNonEmptyProjectName(?string $name = null): void
    {
        $message = 'You provided an invalid Firebase project name ("'.$name.'")';

        if (\is_string($name) && \trim($name) === '') {
            throw new FirebaseError($message.': The project name must be a non-empty string');
        }
    }
}
