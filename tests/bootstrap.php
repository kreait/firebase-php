<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

// Unsafe is needed because google/auth uses getenv/putenv to determine the Application Credentials
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->safeLoad();
