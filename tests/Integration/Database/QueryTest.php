<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Database;

use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\RuleSet;
use Kreait\Firebase\Tests\Integration\DatabaseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

use function current;

/**
 * @internal
 */
#[Group('database-emulator')]
#[Group('emulator')]
final class QueryTest extends DatabaseTestCase
{
    private Reference $ref;

    protected function setUp(): void
    {
        $this->ref = self::$db->getReference(self::$refPrefix);
    }

    #[Test]
    public function limitToLast(): void
    {
        $ref = $this->ref->getChild(__FUNCTION__);

        $rules = self::$db->getRuleSet()->getRules();

        $rules['rules'][$this->ref->getPath()]
            = [__FUNCTION__ => ['.indexOn' => ['key']],
            ];

        self::$db->updateRules(RuleSet::fromArray($rules));

        $ref->push(['key' => 1]);
        $ref->push(['key' => 3]);
        $ref->push(['key' => 2]);

        $value = $ref->orderByChild('key')->limitToLast(1)->getValue();

        $this->assertSame(['key' => 3], current($value));
    }
}
