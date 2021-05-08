<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use Google\Cloud\Firestore\FirestoreClient;

final class Firestore implements Contract\Firestore
{
    private FirestoreClient $client;

    private function __construct(FirestoreClient $client)
    {
        $this->client = $client;
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
