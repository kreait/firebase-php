<?php

declare(strict_types=1);

namespace Kreait\Firebase;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\ApiClient;
use Kreait\Firebase\Database\Reference;
use Kreait\Firebase\Database\RuleSet;
use Kreait\Firebase\Database\Transaction;
use Kreait\Firebase\Database\UrlBuilder;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Psr\Http\Message\UriInterface;

use function ltrim;
use function sprintf;
use function trim;

/**
 * @internal
 */
final class Database implements Contract\Database
{
    private ApiClient $client;
    private UrlBuilder $urlBuilder;
    private UriInterface $uri;

    public function __construct(UriInterface $uri, ApiClient $client, UrlBuilder $urlBuilder)
    {
        $this->uri = $uri;
        $this->client = $client;
        $this->urlBuilder = $urlBuilder;
    }

    public function getReference(?string $path = null): Reference
    {
        if ($path === null || trim($path) === '') {
            $path = '/';
        }

        $path = '/'.ltrim($path, '/');

        try {
            return new Reference($this->uri->withPath($path), $this->client, $this->urlBuilder);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getReferenceFromUrl($uri): Reference
    {
        $uri = $uri instanceof UriInterface ? $uri : new Uri($uri);

        if (($givenHost = $uri->getHost()) !== ($dbHost = $this->uri->getHost())) {
            throw new InvalidArgumentException(sprintf(
                'The given URI\'s host "%s" is not covered by the database for the host "%s".',
                $givenHost,
                $dbHost,
            ));
        }

        return $this->getReference($uri->getPath());
    }

    public function getRuleSet(): RuleSet
    {
        $rules = $this->client->get('/.settings/rules');

        return RuleSet::fromArray($rules);
    }

    public function updateRules(RuleSet $ruleSet): void
    {
        $this->client->updateRules('/.settings/rules', $ruleSet);
    }

    public function runTransaction(callable $callable): mixed
    {
        $transaction = new Transaction($this->client);

        return $callable($transaction);
    }
}
