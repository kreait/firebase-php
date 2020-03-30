<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Database;

use Kreait\Firebase\Database\RuleSet;
use Kreait\Firebase\Tests\Integration\DatabaseTestCase;

/**
 * @internal
 */
class RuleSetTest extends DatabaseTestCase
{
    public function testDefault(): void
    {
        $ruleSet = RuleSet::default();

        self::$db->updateRules($ruleSet);

        $this->assertEquals($ruleSet, self::$db->getRuleSet());
    }

    public function testPublic(): void
    {
        $ruleSet = RuleSet::public();

        self::$db->updateRules($ruleSet);

        $this->assertEquals($ruleSet, self::$db->getRuleSet());
    }

    public function testPrivate(): void
    {
        $ruleSet = RuleSet::private();

        self::$db->updateRules($ruleSet);

        $this->assertEquals($ruleSet, self::$db->getRuleSet());
    }
}
