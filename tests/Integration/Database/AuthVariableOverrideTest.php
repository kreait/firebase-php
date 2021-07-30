<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Database;

use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Database\RuleSet;
use Kreait\Firebase\Exception\Database\PermissionDenied;
use Kreait\Firebase\Tests\Integration\DatabaseTestCase;

/**
 * @internal
 */
class AuthVariableOverrideTest extends DatabaseTestCase
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

    public function testItCanAccessAReferenceThatBelongsToTheSameUser(): void
    {
        $uid = $this->auth->signInAnonymously()->firebaseUserId();

        $this->publishRules(__FUNCTION__, ['.read' => 'auth.uid === "'.$uid.'"']);

        try {
            $db = $this->databaseWithAuthOverride(['uid' => $uid]);

            $db->getReference(self::$refPrefix)->getChild(__FUNCTION__)->getValue();
            $this->addToAssertionCount(1);
        } finally {
            $this->deleteUser($uid);
        }
    }

    public function testItCanNotAccessAReferenceThatRequiresAnotherUser(): void
    {
        $uid = $this->auth->signInAnonymously()->firebaseUserId();

        $this->publishRules(__FUNCTION__, ['.read' => 'auth.uid === "someone-else"']);

        try {
            $db = $this->databaseWithAuthOverride(['uid' => $uid]);

            $this->expectException(PermissionDenied::class);
            $db->getReference(self::$refPrefix)->getChild(__FUNCTION__)->getValue();
        } finally {
            $this->deleteUser($uid);
        }
    }

    public function testItCanAccessAPublicReferenceWhenAuthOverrideIsSetToBeUnauthenticated(): void
    {
        $uid = $this->auth->signInAnonymously()->firebaseUserId();

        $this->publishRules(__FUNCTION__, ['.read' => true]);

        try {
            $db = $this->databaseWithAuthOverride(null);

            $ref = $db->getReference(self::$refPrefix)->getChild(__FUNCTION__);
            $ref->getValue();
            $this->addToAssertionCount(1);
        } finally {
            $this->deleteUser($uid);
        }
    }

    public function testWhenUnauthenticatedItCanNotAccessAReferenceThatRequiresAuthentication(): void
    {
        $uid = $this->auth->signInAnonymously()->firebaseUserId();

        $this->publishRules(__FUNCTION__, ['.read' => 'auth != null']);

        try {
            $db = $this->databaseWithAuthOverride(null);

            $this->expectException(PermissionDenied::class);
            $db->getReference(self::$refPrefix)->getChild(__FUNCTION__)->getValue();
        } finally {
            $this->deleteUser($uid);
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
     * @param array<string, mixed>|null $override
     */
    private function databaseWithAuthOverride(?array $override): Database
    {
        return self::$factory
            ->withDatabaseUri(self::$rtdbUrl)
            ->withDatabaseAuthVariableOverride($override)
            ->createDatabase()
        ;
    }
}
