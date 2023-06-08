<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Firestore;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FirestoreTest extends TestCase
{
    /**
     * @test
     */
    public function itReturnsTheSameClientItWasGiven(): void
    {
        $client = $this->createMock(FirestoreClient::class);
        $firestore = Firestore::withFirestoreClient($client);

        $this->assertSame($client, $firestore->database());
    }
}
