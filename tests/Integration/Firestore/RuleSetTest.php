<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Firestore;

use Kreait\Firebase\Firestore\RuleSet;
use Kreait\Firebase\Tests\Integration\FirestoreTestCase;

class RuleSetTest extends FirestoreTestCase
{
    public function testDefault()
    {
        $ruleSet = RuleSet::default();

        self::$db->updateRules($ruleSet);

        $this->assertEquals($ruleSet, self::$db->getRules());
    }

    public function testPublic()
    {
        $ruleSet = RuleSet::public();

        self::$db->updateRules($ruleSet);

        $this->assertEquals($ruleSet, self::$db->getRules());
    }

    public function testPrivate()
    {
        $ruleSet = RuleSet::private();

        self::$db->updateRules($ruleSet);

        $this->assertEquals($ruleSet, self::$db->getRules());
    }
}
