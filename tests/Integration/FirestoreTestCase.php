<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Firestore;
use Kreait\Firebase\Tests\IntegrationTestCase;

abstract class FirestoreTestCase extends IntegrationTestCase
{
    /**
     * @var string
     */
    protected static $testCollection;

    /**
     * @var Firestore
     */
    protected static $db;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$db = self::$firebase->getFirestore();
        self::$testCollection = 'tests';

        try {
            self::$db->getCollection(self::$testCollection)->remove();
        }
        catch (\Exception $e) {
            // assuming it just doesn't exist yet, continue with tests
        }
    }
}
