<?php

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Auth\AuthCredential;
use Kreait\Firebase\Auth\AuthProvider;

class TwitterAuthProvider extends AuthProvider
{
    protected static $PROVIDER_ID;

    public function __construct() {}

    public static function credential(string $token, string $secret): AuthCredential
    {
        // To Do
    }

    public function setCustomParameters($customOAuthParameters): AuthProvider
    {
        //To Do
    }
}
