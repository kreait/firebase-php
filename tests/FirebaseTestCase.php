<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests;

use PHPUnit\Framework\TestCase;

abstract class FirebaseTestCase extends TestCase
{
    protected static $fixturesDir = __DIR__.'/_fixtures';
}
