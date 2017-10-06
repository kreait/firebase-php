<?php

namespace Kreait\Firebase\Auth\Providers;

interface AuthProvider
{
    public function getProviderId();
    public function setProviderId(string $providerId);
}
