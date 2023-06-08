<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Google\Cloud\Firestore\FirestoreClient;

/**
 * @internal
 */
final class Firestore implements Contract\Firestore
{
    private function __construct(private readonly FirestoreClient $client)
    {
    }

    public static function withFirestoreClient(FirestoreClient $firestoreClient): self
    {
        return new self($firestoreClient);
    }

    public function database(): FirestoreClient
    {
        return $this->client;
    }
}
