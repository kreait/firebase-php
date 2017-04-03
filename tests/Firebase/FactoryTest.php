<?php

namespace Tests\Firebase;

use Firebase\Auth\Token\Domain\Generator;
use Firebase\Auth\Token\Domain\Verifier;
use Firebase\Auth\Token\Handler;
use Firebase\Exception\LogicException;
use Firebase\Factory;
use Firebase\V3\Firebase;
use Google\Auth\CredentialsLoader;
use Lcobucci\JWT\Token;
use Tests\FirebaseTestCase;

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
