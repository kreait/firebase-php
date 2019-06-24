<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration\Database;

use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\Transaction;
use Kreait\Firebase\Exception\ApiException;
use Kreait\Firebase\Exception\Database\TransactionFailed;
use Kreait\Firebase\Tests\Integration\DatabaseTestCase;

/**
 * @internal
 */
class TransactionTest extends DatabaseTestCase
{
    /**
     * @var Reference
     */
    private $ref;

    protected function setUp()
    {
        $this->ref = self::$db->getReference(self::$refPrefix);
    }

    public function testAValueCanBeWritten()
    {
        $ref = $this->ref->getChild(__FUNCTION__);

        self::$db->runTransaction(static function (Transaction $transaction) use ($ref) {
            $transaction->snapshot($ref);

            $transaction->set($ref, 'new value');
        });

        $this->assertSame('new value', $ref->getValue());
    }

    public function testATransactionPreventsAChangeWhenTheRemoteHasChanged()
    {
        $firstRef = $this->ref->getChild(__FUNCTION__);
        $firstRef->set(['key' => 'value']);

        $this->expectException(TransactionFailed::class);

        self::$db->runTransaction(static function (Transaction $transaction) use ($firstRef) {
            // Register a transaction for the given reference
            $transaction->snapshot($firstRef);

            // Set the value without a transaction
            $firstRef->set('new value');

            // This should fail
            $transaction->set($firstRef, 'new value');
        });
    }

    public function testATransactionKeepsTrackOfMultipleReferences()
    {
        $firstRef = $this->ref->getChild(__FUNCTION__.'_first');
        $secondRef = $this->ref->getChild(__FUNCTION__.'_second');

        $this->expectException(TransactionFailed::class);

        self::$db->runTransaction(function (Transaction $transaction) use ($firstRef, $secondRef) {
            // Register a transaction for the given reference
            $firstSnapshot = $transaction->snapshot($firstRef);
            $secondSnapshot = $transaction->snapshot($secondRef);

            $firstCurrentValue = $firstSnapshot->getValue() ?: 0;
            $newFirstValue = ++$firstCurrentValue;

            $secondCurrentValue = $secondSnapshot->getValue() ?: 0;
            $newSecondValue = ++$secondCurrentValue;

            // Set the value without a transaction
            $firstRef->set($newFirstValue);
            $secondRef->set($newSecondValue);

            // A transactional "set" will now fail
            try {
                $transaction->set($firstRef, $newFirstValue);
                $this->fail('An exception should have been thrown');
            } catch (ApiException $e) {
                // this is expected
            }

            $transaction->set($secondRef, $newSecondValue);
        });
    }

    public function testAValueCanBeDeleted()
    {
        $ref = $this->ref->getChild(__FUNCTION__);

        self::$db->runTransaction(static function (Transaction $transaction) use ($ref) {
            $transaction->snapshot($ref);

            $transaction->remove($ref);
        });

        $this->assertTrue($noExceptionHasBeenThrown = true);
    }

    public function testATransactionPreventsADeletionWhenTheRemoteHasChanged()
    {
        $firstRef = $this->ref->getChild(__FUNCTION__);
        $firstRef->set(['key' => 'value']);

        $this->expectException(TransactionFailed::class);

        self::$db->runTransaction(static function (Transaction $transaction) use ($firstRef) {
            // Register a transaction for the given reference
            $transaction->snapshot($firstRef);

            // Set the value without a transaction
            $firstRef->set('new value');

            // This should fail
            $transaction->remove($firstRef);
        });
    }
}
