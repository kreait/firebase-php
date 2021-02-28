<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Integration;

use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Tests\IntegrationTestCase;
use Psr\Log\Test\TestLogger;

/**
 * @internal
 */
class HttpLoggingTest extends IntegrationTestCase
{
    /** @var TestLogger */
    private $logger;

    /** @var TestLogger */
    private $debugLogger;

    /** @var Auth */
    private $auth;

    /** @var Auth */
    private $authWithLogger;

    /** @var Auth */
    private $authWithDebugLogger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new TestLogger();
        $this->debugLogger = new TestLogger();

        $this->auth = self::$factory->createAuth();
        $this->authWithLogger = self::$factory->withHttpLogger($this->logger)->createAuth();
        $this->authWithDebugLogger = self::$factory->withEnabledDebug($this->debugLogger)->createAuth();
    }

    public function testItLogsSuccesses(): void
    {
        $user = $this->auth->createAnonymousUser();

        try {
            $this->authWithLogger->getUser($user->uid);
            $this->assertCount(1, $this->logger->records);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }

    public function testItLogsFailures(): void
    {
        try {
            $this->authWithDebugLogger->updateUser('does-not-exist', []);
        } catch (\Throwable $e) {
        } finally {
            $this->assertTrue($this->debugLogger->hasNoticeThatContains('USER_NOT_FOUND'));
        }
    }

    public function testItUsesAHttpDebugLogger(): void
    {
        $user = $this->auth->createAnonymousUser();

        try {
            $this->authWithDebugLogger->getUser($user->uid);
            $this->assertCount(1, $this->debugLogger->records);
        } finally {
            $this->auth->deleteUser($user->uid);
        }
    }
}
