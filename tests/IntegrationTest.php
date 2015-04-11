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

    protected function setUp()
    {
        $r = new \ReflectionClass($this);
        $shortClassName = $r->getShortName();

        $this->baseUrl = getenv('FIREBASE_HOST');
        $this->baseLocation = sprintf('%s/%s', getenv('FIREBASE_BASE_LOCATION'), $shortClassName);
        $this->recordingMode = (int) getenv('FIREBASE_TAPE_RECORDER_RECORDING_MODE');

        $this->configuration = new Configuration();

        $this->fixturesDir = sprintf('%s/%s/%s', __DIR__, getenv('FIREBASE_TAPE_RECORDER_TAPES_DIR'), $r->getShortName());

        $this->setHttpAdapter();
    }

    protected function tearDown()
    {
        if ($this->recorder) {
            $this->recorder->eject();
        }
    }

    protected function setHttpAdapter()
    {
        $this->recorder = new TapeRecorderSubscriber($this->fixturesDir);
        $this->recorder->setRecordingMode($this->recordingMode);

        $http = HttpAdapterFactory::guess();

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber($this->recorder);

        $this->http = new EventDispatcherHttpAdapter($http, $eventDispatcher);



        $configuration = new Configuration();
        $configuration->setHttpAdapter($this->http);

        $this->firebase = new Firebase($this->baseUrl, $configuration);
    }

    protected function getLocation($subLocation = null)
    {
        if (!$subLocation) {
            return $this->baseLocation;
        }

        return $this->baseLocation.'/'.$subLocation;
    }
}
