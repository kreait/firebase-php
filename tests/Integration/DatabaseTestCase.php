<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Tests\IntegrationTestCase;

abstract class DatabaseTestCase extends IntegrationTestCase
{
    /** @var string */
    protected static $refPrefix;

    /** @var Database */
    protected static $db;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$db = self::$factory->createDatabase();
        self::$refPrefix = 'tests'.\bin2hex(\random_bytes(5));

        self::$db->getReference(self::$refPrefix)->remove();
    }
}
