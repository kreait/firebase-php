<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Firestore;

use Kreait\Firebase\Firestore\RuleSet;
use Kreait\Firebase\Tests\Integration\FirestoreTestCase;

class RuleSetTest extends FirestoreTestCase
{
    public function testDefault()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');

        $ruleSet = RuleSet::default();

        self::$db->updateRules($ruleSet);

        $this->assertEquals($ruleSet, self::$db->getRules());
    }

    public function testPublic()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');

        $ruleSet = RuleSet::public();

        self::$db->updateRules($ruleSet);

        $this->assertEquals($ruleSet, self::$db->getRules());
    }

    public function testPrivate()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');

        $ruleSet = RuleSet::private();

        self::$db->updateRules($ruleSet);

        $this->assertEquals($ruleSet, self::$db->getRules());
    }
}
