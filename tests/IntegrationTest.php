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

use Ivory\HttpAdapter\CurlHttpAdapter;
use Ivory\HttpAdapter\Event\Subscriber\TapeRecorderSubscriber;
use Ivory\HttpAdapter\HttpAdapterInterface;

abstract class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Firebase
     */
    protected $firebase;

    /**
     * @var HttpAdapterInterface
     */
    protected $http;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $baseLocation;

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
        $this->baseUrl = getenv('FIREBASE_HOST');
        $this->baseLocation = getenv('FIREBASE_BASE_LOCATION');
        $this->recordingMode = (int) getenv('FIREBASE_TEST_RECORDING_MODE');

        $r = new \ReflectionClass($this);
        $this->fixturesDir = __DIR__.'/fixtures/'.$r->getShortName();

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
        $this->http = new CurlHttpAdapter();

        $this->recorder = new TapeRecorderSubscriber($this->fixturesDir);
        $this->recorder->setRecordingMode($this->recordingMode);

        $this->http->getConfiguration()->getEventDispatcher()->addSubscriber($this->recorder);

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
