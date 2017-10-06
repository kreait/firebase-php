<?php

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Auth\AuthCredential;
use Kreait\Firebase\Auth\AuthProvider;

class FacebookAuthProvider extends AuthProvider
{
    protected static $PROVIDER_ID;

    public function __construct() {}

    public static function credential(string $token): AuthCredential
    {
        // To Do
    }
}
