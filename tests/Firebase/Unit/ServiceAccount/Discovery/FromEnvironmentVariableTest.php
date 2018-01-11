<?php

namespace Kreait\Tests\Firebase\Unit\ServiceAccount\Discovery;

use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\ServiceAccount\Discovery\FromEnvironmentVariable;
use Kreait\Tests\Firebase\Unit\UnitTestCase;

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
        putenv($this->envVarName);
    }

    public function testItWorks()
    {
        putenv(sprintf('%s=%s', $this->envVarName, $this->fixturesDir.'/ServiceAccount/valid.json'));

        $sut = new FromEnvironmentVariable($this->envVarName);
        $sut();

        $this->assertTrue($noExceptionWasThrown = true);
    }

    public function testItKnowWhenTheVariableIsNotSet()
    {
        $this->expectException(ServiceAccountDiscoveryFailed::class);

        $sut = new FromEnvironmentVariable('undefined');
        $sut();
    }

    public function testItKnowWhenTheVariableHasAValueCausingAnError()
    {
        putenv(sprintf('%s=%s', $this->envVarName, $this->fixturesDir.'/ServiceAccount/invalid.json'));

        $this->expectException(ServiceAccountDiscoveryFailed::class);

        $sut = new FromEnvironmentVariable($this->envVarName);
        $sut();
    }
}
