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
    protected static $refPrefix;

    /**
     * @var Firestore
     */
    protected static $db;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$db = self::$firebase->getFirestore();
        self::$refPrefix = 'tests';

        self::$db->getReference(self::$refPrefix)->remove();
    }
}
