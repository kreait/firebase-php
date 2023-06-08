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
    /**
     * @test
     */
    public function customWithMissingRulesKey(): void
    {
        $data = ['rules' => [
            '.read' => 'auth != null',
            '.write' => 'auth != null',
        ]];

        $ruleSet = RuleSet::fromArray($data['rules']);

        $this->assertEqualsCanonicalizing($data, $ruleSet->getRules());
    }
}
