<?php

namespace Kreait;

use Firebase\Auth\Token\Handler as TokenHandler;
use GuzzleHttp\Psr7;
use Kreait\Firebase\Auth\User;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Http;
use Kreait\Firebase\ServiceAccount;
use Psr\Http\Message\UriInterface;

class Firebase
{
    /**
     * @var UriInterface
     */
    private $databaseUri;

    /**
     * @var Database|null
     */
    private $database;

    /**
     * @var TokenHandler
     */
    private $tokenHandler;

    /**
     * @var Firebase\Auth|null
     */
    private $auth;

    /**
     * @var Factory
     */
    private $factory;

    public function __construct(
        ServiceAccount $serviceAccount,
        UriInterface $databaseUri,
        TokenHandler $tokenHandler = null,
        Firebase\Auth $auth = null,
        Factory $factory = null
    ) {
        $this->databaseUri = $databaseUri;
        $this->tokenHandler = $tokenHandler;
        $this->auth = $auth;

        $factory = ($factory ?: new Factory())
            ->withServiceAccount($serviceAccount)
            ->withDatabaseUri($databaseUri)
            ->withTokenHandler($tokenHandler);

        $this->factory = $factory;
    }

    /**
     * Returns a new instance with a changed database URI.
     *
     * @param string|UriInterface $databaseUri
     *
     * @return Firebase
     */
    public function withDatabaseUri($databaseUri): self
    {
        $new = clone $this;

        $new->databaseUri = Psr7\uri_for($databaseUri);
        $new->database = null;

        return $new;
    }

    /**
     * Returns an instance of the realtime database.
     *
     * @return Database
     */
    public function getDatabase(): Database
    {
        if (!$this->database) {
            $this->database = $this->factory->createDatabase();
        }

        return $this->database;
    }

    /**
     * Returns an Auth instance.
     *
     * @return Firebase\Auth
     */
    public function getAuth(): Firebase\Auth
    {
        if (!$this->auth) {
            $this->auth = $this->factory->createAuth();
        }

        return $this->auth;
    }

    /**
     * Returns a new instance with the permissions
     * of the user with the given UID and claims.
     *
     * @param User|mixed $user
     * @param array $claims
     *
     * @return Firebase
     */
    public function asUserWithClaims($user, array $claims = []): self
    {
        if ($user instanceof User) {
            $uid = $user->getUid();
        } else {
            $uid = (string) $user;
        }

        return $this->withCustomAuth(new Http\Auth\CustomToken($uid, $claims));
    }

    /**
     * Returns a new instance with the permissions of the given user.
     *
     * @param User|mixed $user
     *
     * @return Firebase
     */
    public function asUser($user): self
    {
        return $this->asUserWithClaims($user);
    }

    /**
     * @deprecated 3.2 use {@see \Kreait\Firebase\Auth::createCustomToken()} or {@see \Kreait\Firebase\Auth::verifyIdToken()} instead
     */
    public function getTokenHandler(): TokenHandler
    {
        trigger_error(
            'The token handler is deprecated and will be removed in release 4.0 of this library.'
            .' Use Firebase\Auth::createCustomToken() or Firebase\Auth::verifyIdToken() instead.',
            E_USER_DEPRECATED
        );

        if (!$this->tokenHandler) {
            $this->tokenHandler = $this->factory->getTokenHandler();
        }

        return $this->tokenHandler;
    }

    private function withCustomAuth(Http\Auth $override): self
    {
        $new = clone $this;
        $new->database = $this->getDatabase()->withCustomAuth($override);

        return $new;
    }
}
