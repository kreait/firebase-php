<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\ServiceAccount\Discovery;

use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount\Discovery\FromEnvironmentVariable;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
class FromEnvironmentVariableTest extends UnitTestCase
{
    /**
     * @var string
     */
    private $envVarName;

    protected function setUp()
    {
        $this->envVarName = 'FIREBASE_FROM_ENV_VAR_TEST';
    }

    protected function tearDown()
    {
        \putenv($this->envVarName);
    }

    public function testItWorksWithAFile()
    {
        \putenv(\sprintf('%s=%s', $this->envVarName, self::$fixturesDir.'/ServiceAccount/valid.json'));

        $sut = new FromEnvironmentVariable($this->envVarName);
        $sut();

        $this->addToAssertionCount(1);
    }

    public function testItWorksWithAJsonString()
    {
        $json = \json_encode(
            \json_decode(
                (string) \file_get_contents(self::$fixturesDir.'/ServiceAccount/valid.json'), true
            )
        );

        \putenv(\sprintf('%s=%s', $this->envVarName, $json));

        $sut = new FromEnvironmentVariable($this->envVarName);
        $sut();

        $this->addToAssertionCount(1);
    }

    public function testItRejectsAnInvalidJsonString()
    {
        \putenv(\sprintf('%s=%s', $this->envVarName, '{'));

        $this->expectException(ServiceAccountDiscoveryFailed::class);
        $sut = new FromEnvironmentVariable($this->envVarName);
        $sut();
    }

    public function testItKnowWhenTheVariableIsNotSet()
    {
        $this->expectException(ServiceAccountDiscoveryFailed::class);

        $sut = new FromEnvironmentVariable('undefined');
        $sut();
    }

    public function testItKnowWhenTheVariableHasAValueCausingAnError()
    {
        \putenv(\sprintf('%s=%s', $this->envVarName, self::$fixturesDir.'/ServiceAccount/invalid.json'));

        $this->expectException(ServiceAccountDiscoveryFailed::class);

        $sut = new FromEnvironmentVariable($this->envVarName);
        $sut();
    }
}
