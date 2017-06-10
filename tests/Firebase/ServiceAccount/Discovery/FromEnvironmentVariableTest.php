<?php

namespace Kreait\Tests\Firebase\ServiceAccount\Discovery;

use Kreait\Firebase\Exception\ServiceAccountDiscoveryFailed;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\ServiceAccount\Discovery\FromEnvironmentVariable;
use Kreait\Tests\FirebaseTestCase;

class FromEnvironmentVariableTest extends FirebaseTestCase
{
    private $var = 'FIREBASE_FROM_ENV_VAR_TEST';

    /**
     * @var FromEnvironmentVariable
     */
    private $method;

    protected function setUp()
    {
        $this->method = new FromEnvironmentVariable($this->var);
    }

    protected function tearDown()
    {
        putenv($this->var);
    }

    public function testItWorks()
    {
        putenv(sprintf('%s=%s', $this->var, $this->fixturesDir.'/ServiceAccount/valid.json'));

        $this->assertInstanceOf(ServiceAccount::class, ($this->method)());
    }

    public function testItKnowWhenTheVariableIsNotSet()
    {
        $this->expectException(ServiceAccountDiscoveryFailed::class);
        (new FromEnvironmentVariable('undefined'))();
    }

    public function testItKnowWhenTheVariableHasAValueCausingAnError()
    {
        $this->expectException(ServiceAccountDiscoveryFailed::class);

        putenv(sprintf('%s=%s', $this->var, $this->fixturesDir.'/ServiceAccount/invalid.json'));

        ($this->method)();
    }
}
