<?php

namespace Kreait\Tests\Firebase;

use Firebase\Auth\Token\Handler;
use Google\Auth\CredentialsLoader;
use Kreait\Firebase;
use Kreait\Firebase\Exception\LogicException;
use Kreait\Firebase\Factory;
use Kreait\Tests\FirebaseTestCase;

class FactoryTest extends FirebaseTestCase
{
    /**
     * @var string
     */
    private $keyFile;

    protected function setUp()
    {
        $this->keyFile = $this->fixturesDir.'/ServiceAccount/valid.json';

        // Unset all eligible environment variables
        putenv(Factory::ENV_VAR);
        putenv(CredentialsLoader::ENV_VAR);
        putenv('HOME'); // This will let the Google CredentialsLoader return null
    }

    public function testItFindsCredentialsFromTheFirebaseEnvVar()
    {
        putenv(sprintf('%s=%s', Factory::ENV_VAR, $this->keyFile));

        $this->assertInstanceOf(Firebase::class, (new Factory())->create());
    }

    public function testItFindsCredentialsFromTheGoogleApplicationCredentialsEnvVar()
    {
        putenv(sprintf('%s=%s', CredentialsLoader::ENV_VAR, $this->keyFile));

        $this->assertInstanceOf(Firebase::class, (new Factory())->create());
    }

    public function testItFindsACustomCredentialsFile()
    {
        $firebase = (new Factory())
            ->withCredentials($this->fixturesDir.'/ServiceAccount/custom.json')
            ->create();

        $object = new \ReflectionObject($firebase);
        $property = $object->getProperty('serviceAccount');
        $property->setAccessible(true);
        $serviceAccount = $property->getValue($firebase);

        $this->assertSame('custom', $serviceAccount->getProjectId());
    }

    public function testItThrowsAnExceptionWhenNoCredentialsAreAvailable()
    {
        $this->expectException(LogicException::class);

        (new Factory())->create();
    }

    public function testItTreatsInvalidPathsAsNonExistent()
    {
        $this->expectException(LogicException::class);

        (new Factory())
            ->withCredentials('foobar')
            ->create();
    }

    public function testItAcceptsACustomDatabaseUri()
    {
        putenv(sprintf('%s=%s', Factory::ENV_VAR, $this->keyFile));

        $factory = (new Factory())
            ->withDatabaseUri('http://domain.tld')
            ->create();

        $this->assertInstanceOf(Firebase::class, $factory);
    }

    public function testItUsesADefaultTokenHandler()
    {
        $firebase = (new Factory())
            ->withCredentials($this->keyFile)
            ->create();

        $this->assertInstanceOf(Handler::class, $firebase->getTokenHandler());
    }

    public function testItAcceptsACustomTokenHandler()
    {
        $handler = new Handler('projectId', 'clientEmail', 'privateKey');

        $firebase = (new Factory())
            ->withCredentials($this->keyFile)
            ->withTokenHandler($handler)
            ->create();

        $this->assertSame($handler, $firebase->getTokenHandler());
    }
}
