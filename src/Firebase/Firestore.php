<?php

namespace Kreait\Firebase;

use Kreait\Firebase\Firestore\ApiClient;
use Kreait\Firebase\Firestore\Collection;
use Kreait\Firebase\Firestore\RuleSet;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\OutOfRangeException;
use Psr\Http\Message\UriInterface;
use function GuzzleHttp\Psr7\uri_for;
use GuzzleHttp\Psr7\Uri;

/**
 * The Firebase Realtime Database.
 *
 * @see https://firebase.google.com/docs/reference/js/firebase.database.Database
 */
class Firestore
{
    const SERVER_TIMESTAMP = ['.sv' => 'timestamp'];

    /**
     * @var ApiClient
     */
    private $client;

    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * Creates a new database instance for the given database URI
     * which is accessed by the given API client.
     *
     * @param UriInterface $uri
     * @param ApiClient $client
     */
    public function __construct(UriInterface $uri, ApiClient $client)
    {
        $this->uri = $uri;
        $this->client = $client;
    }

    /**
     * Returns a collection.
     *
     * @param string $name
     *
     * @throws InvalidArgumentException
     *
     * @return Reference
     */
    public function getCollection(string $path = ''): Collection
    {
        try {
            return new Collection(Uri::resolve($this->uri, $path), $this->client);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Retrieve Firebase Database Rules.
     *
     * @see https://firebase.google.com/docs/database/rest/app-management#retrieving-firebase-realtime-database-rules
     *
     * @return RuleSet
     */
    public function getRules(): RuleSet
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $rules = $this->client->get($this->uri->withPath('.settings/rules'));

        return RuleSet::fromArray($rules);
    }

    /**
     * Update Firebase Database Rules.
     *
     * @see https://firebase.google.com/docs/database/rest/app-management#updating-firebase-realtime-database-rules
     *
     * @param RuleSet $ruleSet
     */
    public function updateRules(RuleSet $ruleSet)
    {
        /* @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->client->set($this->uri->withPath('.settings/rules'), $ruleSet);
    }
}
