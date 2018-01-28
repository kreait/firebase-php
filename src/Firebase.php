<?php

namespace Kreait;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Database;

class Firebase
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var Auth
     */
    private $auth;

    public function __construct(Database $database, Auth $auth)
    {
        $this->database = $database;
        $this->auth = $auth;
    }

    /**
     * Returns an instance of the realtime database.
     *
     * @return Database
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * Returns an Auth instance.
     *
     * @return Auth
     */
    public function getAuth(): Auth
    {
        return $this->auth;
    }
}
