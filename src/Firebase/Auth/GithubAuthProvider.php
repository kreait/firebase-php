<?php

namespace Kreait\Firebase\Auth;

use Kreait\Firebase\Auth\AuthCredential;
use Kreait\Firebase\Auth\AuthProvider;

class GithubAuthProvider extends AuthProvider
{
    protected static $PROVIDER_ID;

    public function __construct() {}

    public static function credential(string $token): AuthCredential
    {
        // To Do
    }

    public function addScope(string $scope): AuthProvider
    {
        //To Do
    }

    public function setCustomParameters($customOAuthParameters): AuthProvider
    {
        //To Do
    }
}
