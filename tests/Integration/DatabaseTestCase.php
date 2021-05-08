<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Kreait\Firebase\Util;
use Kreait\Firebase\Util\JSON;
use Throwable;

abstract class DatabaseTestCase extends IntegrationTestCase
{
    protected static string $refPrefix;

    protected static ?string $rtdbUrl;

    protected static Database $db;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$rtdbUrl = self::rtdbUrlFromEnvironment() ?? self::rtdbUrlFromFile();

        if (!self::$rtdbUrl) {
            self::markTestSkipped('The database tests require a database uri');
        }

        self::$db = self::$factory->withDatabaseUri(self::$rtdbUrl)->createDatabase();
        self::$refPrefix = 'tests'.\bin2hex(\random_bytes(5));

        self::$db->getReference(self::$refPrefix)->remove();
    }

    private static function rtdbUrlFromFile(): ?string
    {
        $path = self::$fixturesDir.'/test_rtdb.json';

        if (!\file_exists($path)) {
            return null;
        }

        try {
            if ($contents = \file_get_contents($path)) {
                return JSON::decode($contents, true);
            }

            return null;
        } catch (Throwable $e) {
            return null;
        }
    }

    private static function rtdbUrlFromEnvironment(): ?string
    {
        return Util::getenv('TEST_FIREBASE_RTDB_URI');
    }
}
