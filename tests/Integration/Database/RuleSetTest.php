<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Database;

use Beste\Json;
use Kreait\Firebase\Database\RuleSet;
use Kreait\Firebase\Tests\Integration\DatabaseTestCase;

/**
 * @internal
 *
 * @group database-emulator
 * @group emulator
 */
final class RuleSetTest extends DatabaseTestCase
{
    public function testDefault(): void
    {
        $ruleSet = RuleSet::default();

        self::$db->updateRules($ruleSet);

        self::assertEquals($ruleSet, self::$db->getRuleSet());
    }

    public function testPublic(): void
    {
        $ruleSet = RuleSet::public();

        self::$db->updateRules($ruleSet);

        self::assertEquals($ruleSet, self::$db->getRuleSet());
    }

    public function testPrivate(): void
    {
        $ruleSet = RuleSet::private();

        self::$db->updateRules($ruleSet);

        self::assertEquals($ruleSet, self::$db->getRuleSet());
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/705
     */
    public function testRulesAreProperlyEncoded(): void
    {
        $rules = RuleSet::private()->getRules();
        $rules['rules'][self::$refPrefix . __FUNCTION__] = [
            'value1' => [
                '.indexOn' => [
                    'ab',
                ],
            ],
            'value2' => [
                '.indexOn' => [
                    'cd',
                    'ef',
                ],
            ],
        ];

        $ruleSet = RuleSet::fromArray($rules);

        self::$db->updateRules($ruleSet);

        $response = self::$apiClient
            ->get(
                self::$db->getReference()->getUri()->withPath('/.settings/rules.json'),
            );

        self::assertSame(200, $response->getStatusCode());
        // Assert that the returned JSON doesn't contain objects with integer keys instead of lists
        self::assertStringNotMatchesFormat('/\d:/', (string) $response->getBody());
    }
}
