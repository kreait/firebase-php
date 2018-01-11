<?php

declare(strict_types=1);

namespace Kreait\Tests;

use Kreait\Firebase;
use PHPStan\Testing\TestCase;

/**
 * @group Integration
 */
abstract class IntegrationTestCase extends TestCase
{
    /**
     * @var Firebase
     */
    protected static $firebase;

    public static function setUpBeforeClass()
    {
        $credentialsPath = __DIR__.'/test_credentials.json';

        try {
            $serviceAccount = Firebase\ServiceAccount::fromJsonFile($credentialsPath);
        } catch (\Throwable $e) {
            self::markTestSkipped('The integration tests require a credentials file at "'.$credentialsPath.'"."');

            return;
        }

        self::$firebase = (new Firebase\Factory())
            ->withServiceAccount($serviceAccount)
            ->create();
    }
}
