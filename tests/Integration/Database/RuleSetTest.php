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
    /**
     * @test
     */
    public function default(): void
    {
        $ruleSet = RuleSet::default();

        self::$db->updateRules($ruleSet);

        $this->assertEqualsCanonicalizing($ruleSet->getRules(), self::$db->getRuleSet()->getRules());
    }

    /**
     * @test
     */
    public function public(): void
    {
        $ruleSet = RuleSet::public();

        self::$db->updateRules($ruleSet);

        $this->assertEqualsCanonicalizing($ruleSet->getRules(), self::$db->getRuleSet()->getRules());
    }

    /**
     * @test
     */
    public function private(): void
    {
        $ruleSet = RuleSet::private();

        self::$db->updateRules($ruleSet);

        $this->assertEqualsCanonicalizing($ruleSet->getRules(), self::$db->getRuleSet()->getRules());
    }

    /**
     * @see https://github.com/kreait/firebase-php/issues/705
     *
     * @test
     */
    public function rulesAreProperlyEncoded(): void
    {
        $rules = RuleSet::private()->getRules();
        $rules['rules'][self::$refPrefix.__FUNCTION__] = [
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
            )
        ;

        $this->assertSame(200, $response->getStatusCode());
        // Assert that the returned JSON doesn't contain objects with integer keys instead of lists
        $this->assertStringNotMatchesFormat('/\d:/', (string) $response->getBody());
    }
}
