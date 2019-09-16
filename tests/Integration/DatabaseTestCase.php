<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Database;
use Kreait\Firebase\Tests\IntegrationTestCase;

abstract class DatabaseTestCase extends IntegrationTestCase
{
    /**
     * @var string
     */
    protected static $refPrefix;

    /**
     * @var Database
     */
    protected static $db;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$db = self::$factory->createDatabase();
        self::$refPrefix = 'tests';

        self::$db->getReference(self::$refPrefix)->remove();
    }
}
