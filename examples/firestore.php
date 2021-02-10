<?php

use Kreait\Firebase\Factory;

chdir(dirname(__DIR__));
require_once 'vendor/autoload.php';

$firestore = (new Factory())
    ->withServiceAccount('google-service-account.json')
    ->createFirestore();

$database = $firestore->database();

var_dump($database->get('documents/conversations'));

$database->patch(
    'documents/conversations/1f3f0d72-aec8-4baa-aea1-3962ff62135c',
    [
        'fields' => [
            'modifiedOn' => [
                'timestampValue' => [
                    'seconds' => (new \DateTimeImmutable())->format('U'),
                ]
            ],
            'members' => [
                'arrayValue' => [
                    'values' => [
                        [
                            'mapValue' => [
                                'fields' => [
                                    'email' => [
                                        'stringValue' => 'luke@jedi.com',
                                    ],
                                    'readUntil' => [
                                        'timestampValue' => [
                                            'seconds' => (new \DateTimeImmutable())->format('U'),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    [
        'query' => 'updateMask.fieldPaths=modifiedOn&updateMask.fieldPaths=members',
    ]
);
