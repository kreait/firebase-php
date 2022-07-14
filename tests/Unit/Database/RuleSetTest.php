<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database;

use Kreait\Firebase\Database\RuleSet;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class RuleSetTest extends UnitTestCase
{
    public function testCustomWithMissingRulesKey(): void
    {
        $data = ['rules' => [
            '.read' => 'auth != null',
            '.write' => 'auth != null',
        ]];

        $ruleSet = RuleSet::fromArray($data['rules']);

        $this->assertEquals($data, $ruleSet->getRules());
    }
}
