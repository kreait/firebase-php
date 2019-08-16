<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\RuleSet;
use Kreait\Firebase\Database\Transaction;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\OutOfRangeException;
use Psr\Http\Message\UriInterface;

/**
 * The Firebase Realtime Database.
 *
 * @see https://firebase.google.com/docs/reference/js/firebase.database.Database
 */
class Database
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
     * @internal
     */
    public function __construct(UriInterface $uri, ApiClient $client)
    {
        $this->uri = $uri;
        $this->client = $client;
    }

    /**
     * Returns a Reference to the root or the specified path.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Database#ref
     *
     * @throws InvalidArgumentException
     */
    public function getReference(string $path = null): Reference
    {
        $path = \trim((string) $path);

        if ($path === '') {
            $path = '/';
        }

        try {
            return new Reference($this->uri->withPath($path), $this->client);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns a reference to the root or the path specified in url.
     *
     * @see https://firebase.google.com/docs/reference/js/firebase.database.Database#refFromURL
     *
     * @param string|UriInterface $uri
     *
     * @throws InvalidArgumentException If the URL is invalid
     * @throws OutOfRangeException If the URL is not in the same domain as the current database
     */
    public function getReferenceFromUrl($uri): Reference
    {
        $uri = $uri instanceof UriInterface ? $uri : new Uri($uri);

        if (($givenHost = $uri->getHost()) !== ($dbHost = $this->uri->getHost())) {
            throw new InvalidArgumentException(\sprintf(
                'The given URI\'s host "%s" is not covered by the database for the host "%s".',
                $givenHost, $dbHost
            ));
        }

        return $this->getReference($uri->getPath());
    }

    /**
     * Retrieve Firebase Database Rules.
     *
     * @see https://firebase.google.com/docs/database/rest/app-management#retrieving-firebase-realtime-database-rules
     */
    public function getRules(): RuleSet
    {
        $rules = $this->client->get($this->uri->withPath('.settings/rules'));

        return RuleSet::fromArray($rules);
    }

    /**
     * Update Firebase Database Rules.
     *
     * @see https://firebase.google.com/docs/database/rest/app-management#updating-firebase-realtime-database-rules
     */
    public function updateRules(RuleSet $ruleSet)
    {
        $this->client->updateRules($this->uri->withPath('.settings/rules'), $ruleSet);
    }

    public function runTransaction(callable $callable)
    {
        $transaction = new Transaction($this->client);

        return $callable($transaction);
    }
}
