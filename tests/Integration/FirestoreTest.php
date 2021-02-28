<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Google\Cloud\Firestore\Transaction;
use Kreait\Firebase\Contract\Firestore;
use Kreait\Firebase\Tests\IntegrationTestCase;

/**
 * @group Firestore
 *
 * @internal
 */
final class FirestoreTest extends IntegrationTestCase
{
    /** @var Firestore */
    private $firestore;

    /** @var mixed string */
    private $collectionName;

    protected function setUp(): void
    {
        $this->firestore = self::$factory->createFirestore();
        $this->collectionName = \str_replace('\\', '_', __CLASS__);
    }

    /**
     * @test
     */
    public function it_returns_a_working_firestore_client(): void
    {
        $client = $this->firestore->database();

        $doc = $client->collection($this->collectionName)->document(__METHOD__);

        $doc->set(['counter' => 1]);

        $newCounter = $client->runTransaction(static function (Transaction $transaction) use ($doc) {
            $snapshot = $transaction->snapshot($doc);
            $newCounter = $snapshot['counter'] + 1;

            $transaction->update($doc, [
                ['path' => 'counter', 'value' => $newCounter],
            ]);

            return $newCounter;
        });

        $this->assertSame(2, $newCounter);
    }
}
