<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use GuzzleHttp\Client;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Tests\IntegrationTestCase;

abstract class DatabaseTestCase extends IntegrationTestCase
{
    protected static string $refPrefix;

    protected static Database $db;

    protected static Client $apiClient;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (self::$rtdbUrl === '') {
            self::markTestSkipped('The database tests require a database uri');
        }

        self::$db = self::$factory->createDatabase();
        self::$apiClient = self::$factory->createApiClient(['http_errors' => false]);

        self::$refPrefix = 'tests'.\bin2hex(\random_bytes(5));
        self::$db->getReference(self::$refPrefix)->remove();
    }
}
