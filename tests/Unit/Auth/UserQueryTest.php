<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Auth;

use Beste\Json;
use Kreait\Firebase\Auth\UserQuery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class UserQueryTest extends TestCase
{
    public function testItCanBeComposed(): void
    {
        $expected = [
            'returnUserInfo' => true,
            'sortBy' => 'USER_EMAIL',
            'order' => 'DESC',
            'offset' => 1,
            'limit' => 499,
        ];

        $query = UserQuery::all()
            ->sortedBy(UserQuery::FIELD_USER_EMAIL)
            ->inDescendingOrder()
            ->withOffset(1)
            ->withLimit(499)
        ;

        $this->assertJsonStringEqualsJsonString(Json::encode($expected), Json::encode($query));
    }

    public function testItCanSortInAscendingOrder(): void
    {
        $expected = [
            'returnUserInfo' => true,
            'order' => 'ASC',
        ];

        $query = UserQuery::all()->inAscendingOrder();

        $this->assertJsonStringEqualsJsonString(Json::encode($expected), Json::encode($query));
    }

    public function testItCanSortInDescendingOrder(): void
    {
        $expected = [
            'returnUserInfo' => true,
            'order' => 'DESC',
        ];

        $query = UserQuery::all()->inDescendingOrder();

        $this->assertJsonStringEqualsJsonString(Json::encode($expected), Json::encode($query));
    }

    public function testItCanBeCreatedFromAnArray(): void
    {
        $data = [
            'returnUserInfo' => true,
            'sortBy' => 'USER_EMAIL',
            'order' => 'DESC',
            'offset' => 1,
            'limit' => 499,
        ];

        $query = UserQuery::fromArray($data);

        $this->assertJsonStringEqualsJsonString(Json::encode($data), Json::encode($query));
    }
}
