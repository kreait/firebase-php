<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Auth;

use Kreait\Firebase\Auth\UserQuery;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Tests\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

use function array_values;
use function current;
use function random_int;
use function usleep;

/**
 * @internal
 *
 * @phpstan-import-type UserQueryShape from UserQuery
 */
final class UserQueryTest extends IntegrationTestCase
{
    private Auth $auth;

    protected function setUp(): void
    {
        $this->auth = self::$factory->createAuth();
    }

    #[Test]
    public function sortByField(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        // Another test could have created a user in-between, so we fetch more than we actually need
        $result = $this->auth->queryUsers([
            'sortBy' => UserQuery::FIELD_CREATED_AT,
            'order' => UserQuery::ORDER_DESC,
            'limit' => 10,
        ]);

        try {
            $this->assertUserExists($user, $result);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    #[Test]
    public function ascendingSortOrder(): void
    {
        // Create two users just in case there are no others in the database
        $first = $this->createUserWithEmailAndPassword();
        usleep(1000);
        $second = $this->createUserWithEmailAndPassword();

        $query = [
            'sortBy' => UserQuery::FIELD_CREATED_AT,
            'order' => UserQuery::ORDER_ASC,
            'limit' => 2,
        ];

        $result = array_values($this->auth->queryUsers($query));

        try {
            $this->assertCount(2, $result);
            $this->assertTrue($result[1]->metadata->createdAt >= $result[0]->metadata->createdAt);
        } finally {
            $this->auth->deleteUser($first->uid);
            $this->auth->deleteUser($second->uid);
        }
    }

    #[Test]
    public function descendingSortOrder(): void
    {
        // Create two users just in case there are no others in the database
        $first = $this->createUserWithEmailAndPassword();
        usleep(1000);
        $second = $this->createUserWithEmailAndPassword();

        $query = [
            'sortBy' => UserQuery::FIELD_CREATED_AT,
            'order' => UserQuery::ORDER_DESC,
            'limit' => 2,
        ];

        $result = array_values($this->auth->queryUsers($query));

        try {
            $this->assertCount(2, $result);
            $this->assertLessThanOrEqual($result[0]->metadata->createdAt, $result[1]->metadata->createdAt);
        } finally {
            $this->auth->deleteUser($first->uid);
            $this->auth->deleteUser($second->uid);
        }
    }

    #[Test]
    public function limit(): void
    {
        // Create two users just in case there are no others in the database
        $firstUser = $this->createUserWithEmailAndPassword();
        $secondUser = $this->createUserWithEmailAndPassword();

        $query = [
            'limit' => 1,
        ];

        $result = $this->auth->queryUsers($query);

        try {
            $this->assertCount(1, $result);
        } finally {
            $this->auth->deleteUser($firstUser->uid);
            $this->auth->deleteUser($secondUser->uid);
        }
    }

    #[Test]
    public function filterByUid(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        $query = [
            'filter' => [
                'userId' => $user->uid,
            ],
            'limit' => 1,
        ];

        $result = $this->auth->queryUsers($query);
        $found = current($result);

        try {
            $this->assertCount(1, $result);
            $this->assertInstanceOf(UserRecord::class, $found);
            $this->assertSame($user->uid, $found->uid);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    #[Test]
    public function filterByEmail(): void
    {
        $user = $this->createUserWithEmailAndPassword();

        $query = [
            'filter' => [
                'email' => $user->email,
            ],
            'limit' => 1,
        ];

        $result = $this->auth->queryUsers($query);
        $found = current($result);

        try {
            $this->assertCount(1, $result);
            $this->assertInstanceOf(UserRecord::class, $found);
            $this->assertSame($user->email, $found->email);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    #[Test]
    public function filterByPhoneNumber(): void
    {
        $user = $this->auth->createUser([
            'phoneNumber' => '+49'.random_int(90_000_000_000, 99_999_999_999),
        ]);

        $query = [
            'filter' => [
                'phoneNumber' => $user->phoneNumber,
            ],
            'limit' => 1,
        ];

        $result = $this->auth->queryUsers($query);
        $found = current($result);

        try {
            $this->assertCount(1, $result);
            $this->assertInstanceOf(UserRecord::class, $found);
            $this->assertSame($user->phoneNumber, $found->phoneNumber);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    protected function createUserWithEmailAndPassword(?string $email = null, ?string $password = null): UserRecord
    {
        $email ??= self::randomEmail();
        $password ??= self::randomString();

        return $this->auth->createUser([
            'email' => $email,
            'clear_text_password' => $password,
        ]);
    }

    /**
     * @param array<UserRecord> $queryResult
     */
    private function assertUserExists(UserRecord $userRecord, array $queryResult): void
    {
        foreach ($queryResult as $record) {
            if ($record->uid === $userRecord->uid) {
                $this->addToAssertionCount(1);

                return;
            }
        }

        $this->fail('Expected query result to contain a user with UID '.$userRecord->uid);
    }
}
