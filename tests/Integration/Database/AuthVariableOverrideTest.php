<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Database;

use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\RuleSet;
use Kreait\Firebase\Exception\Database\PermissionDenied;
use Kreait\Firebase\Tests\Integration\DatabaseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

use function assert;
use function is_string;

/**
 * @internal
 */
#[Group('emulator')]
final class AuthVariableOverrideTest extends DatabaseTestCase
{
    private Auth $auth;

    protected function setUp(): void
    {
        $this->auth = self::$factory->createAuth();
    }

    protected function tearDown(): void
    {
        self::$db->updateRules(RuleSet::private());

        parent::tearDown();
    }

    #[Test]
    public function itCanAccessAReferenceThatBelongsToTheSameUser(): void
    {
        $uid = $this->auth->signInAnonymously()->firebaseUserId();
        assert(is_string($uid));

        $this->publishRules(__FUNCTION__, ['.read' => 'auth.uid === "'.$uid.'"']);

        try {
            $db = $this->databaseWithAuthOverride(['uid' => $uid]);

            $db->getReference(self::$refPrefix)->getChild(__FUNCTION__)->getValue();
            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    #[Test]
    public function itCanNotAccessAReferenceThatRequiresAnotherUser(): void
    {
        $uid = $this->auth->signInAnonymously()->firebaseUserId();
        assert(is_string($uid));

        $this->publishRules(__FUNCTION__, ['.read' => 'auth.uid === "someone-else"']);

        try {
            $db = $this->databaseWithAuthOverride(['uid' => $uid]);

            $this->expectException(PermissionDenied::class);
            $db->getReference(self::$refPrefix)->getChild(__FUNCTION__)->getValue();
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    #[Test]
    public function itCanAccessAPublicReferenceWhenAuthOverrideIsSetToBeUnauthenticated(): void
    {
        $uid = $this->auth->signInAnonymously()->firebaseUserId();
        assert(is_string($uid));

        $this->publishRules(__FUNCTION__, ['.read' => true]);

        try {
            $this->databaseWithAuthOverride(null)
                ->getReference(self::$refPrefix)
                ->getChild(__FUNCTION__)
                ->getValue()
            ;

            $this->addToAssertionCount(1);
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    #[Test]
    public function whenUnauthenticatedItCanNotAccessAReferenceThatRequiresAuthentication(): void
    {
        $uid = $this->auth->signInAnonymously()->firebaseUserId();
        assert(is_string($uid));

        $this->publishRules(__FUNCTION__, ['.read' => 'auth != null']);

        try {
            $db = $this->databaseWithAuthOverride(null);

            $this->expectException(PermissionDenied::class);
            $db->getReference(self::$refPrefix)->getChild(__FUNCTION__)->getValue();
        } finally {
            $this->auth->deleteUser($uid);
        }
    }

    /**
     * @param array<string, mixed> $permissions
     */
    private function publishRules(string $path, array $permissions): void
    {
        $rules = RuleSet::private()->getRules();
        $rules['rules'][self::$refPrefix] = [
            $path => $permissions,
        ];

        self::$db->updateRules(RuleSet::fromArray($rules));
    }

    /**
     * @param array<non-empty-string, mixed>|null $override
     */
    private function databaseWithAuthOverride(?array $override): Database
    {
        // If the RTDB Url is not set, the database tests are already skipped
        assert(self::$rtdbUrl !== null);

        return self::$factory
            ->withDatabaseUri(self::$rtdbUrl)
            ->withDatabaseAuthVariableOverride($override)
            ->createDatabase()
        ;
    }
}
