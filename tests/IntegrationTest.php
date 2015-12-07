<?php

/*
 * This file is part of the firebase-php package.
 *
 * (c) Jérôme Gamez <jerome@kreait.com>
 * (c) kreait GmbH <info@kreait.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Kreait\Firebase;

use Ivory\HttpAdapter\EventDispatcherHttpAdapter;
use Ivory\HttpAdapter\HttpAdapterFactory;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Kreait\Firebase\Auth\TokenGenerator;
use Kreait\Firebase\Auth\TokenGeneratorInterface;
use Kreait\Ivory\HttpAdapter\Event\Subscriber\TapeRecorderSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Firebase
     */
    protected $firebase;

    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var TokenGeneratorInterface
     */
    protected $authTokenGenerator;

    /**
     * @var HttpAdapterInterface
     */
    protected $http;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $baseLocation;

    /**
     * @var TapeRecorderSubscriber
     */
    protected $recorder;

    /**
     * @var int
     */
    protected $recordingMode;

    /**
     * @var string
     */
    protected $fixturesDir;

    /**
     * @var string
     */
    protected $firebaseSecret;

    protected function setUp()
    {
        $r = new \ReflectionClass($this);
        $shortClassName = $r->getShortName();

        $this->baseUrl = getenv('FIREBASE_HOST');
        $this->baseLocation = sprintf('%s/%s', getenv('FIREBASE_BASE_LOCATION'), $shortClassName);
        $this->recordingMode = (int) getenv('FIREBASE_TAPE_RECORDER_RECORDING_MODE');
        $this->firebaseSecret = getenv('FIREBASE_SECRET');

        $this->configuration = new Configuration();
        $this->configuration->setFirebaseSecret($this->firebaseSecret);

        $this->authTokenGenerator = new TokenGenerator($this->firebaseSecret, true);
        $this->configuration->setAuthTokenGenerator($this->authTokenGenerator);

        $this->fixturesDir = sprintf('%s/%s/%s', __DIR__, getenv('FIREBASE_TAPE_RECORDER_TAPES_DIR'), $r->getShortName());

        $this->recorder = new TapeRecorderSubscriber($this->fixturesDir);
        $this->recorder->setRecordingMode($this->recordingMode);

        $http = HttpAdapterFactory::guess();

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber($this->recorder);

        $this->http = new EventDispatcherHttpAdapter($http, $eventDispatcher);

        $this->configuration->setHttpAdapter($this->http);

        $this->firebase = new Firebase($this->baseUrl, $this->configuration);

        // It's not allowed to use the secret as auth token, but we do it so that we can reuse
        // the requests and responses for the Tape Subscriber
        $reflectionObject = new \ReflectionObject($this->firebase);
        $property = $reflectionObject->getProperty('authToken');
        $property->setAccessible(true);
        $property->setValue($this->firebase, $this->firebaseSecret);
        $property->setAccessible(false);
    }

    protected function tearDown()
    {
        $this->recorder->eject();
    }

    protected function getLocation($subLocation = null)
    {
        if (!$subLocation) {
            return $this->baseLocation;
        }

        return $this->baseLocation.'/'.$subLocation;
    }
}
