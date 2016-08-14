<?php

namespace Firebase\V2;

use Firebase\Database;
use Firebase\Database\ApiClient;
use Firebase\Http\Middleware;
use Firebase\V2\Http\Auth;
use Firebase\V2\Http\Auth\AdminToken;
use Firebase\V2\Http\Auth\CustomToken;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;
use Psr\Http\Message\UriInterface;

class Firebase
{
    /**
     * @var UriInterface
     */
    private $databaseUri;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var Database
     */
    private $database;

    public function __construct(UriInterface $databaseUri, string $secret, Auth $auth)
    {
        $this->databaseUri = $databaseUri;
        $this->secret = $secret;
        $this->auth = $auth;
    }

    public static function fromDatabaseUriAndSecret($databaseUri, string $secret): Firebase
    {
        $auth = new AdminToken($secret);

        return new self(Psr7\uri_for($databaseUri), $secret, $auth);
    }

    public function getDatabase(): Database
    {
        if (!$this->database) {
            $this->database = $this->createDatabase();
        }

        return $this->database;
    }

    public function asUserWithClaims(string $uid, array $claims = []): Firebase
    {
        return $this->withCustomAuth(new CustomToken($this->secret, $uid, $claims));
    }

    private function withCustomAuth(Auth $override): Firebase
    {
        return new self($this->databaseUri, $this->secret, $override);
    }

    private function createDatabase(): Database
    {
        $client = $this->createDatabaseClient();

        return new Database($this->databaseUri, $client);
    }

    private function createDatabaseClient(): ApiClient
    {
        $stack = HandlerStack::create();
        $stack->push(Middleware::ensureJson(), 'ensure_json');
        $stack->push(Middleware::overrideAuth($this->auth), 'legacy_auth');

        $http = new Client([
            'base_uri' => $this->databaseUri,
            'handler' => $stack,
        ]);

        return new ApiClient($http);
    }
}
