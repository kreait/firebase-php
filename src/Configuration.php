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
use Ivory\HttpAdapter\HttpAdapterInterface;
use Kreait\Firebase\Exception\ConfigurationException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    private $firebaseSecret;

    /**
     * @var HttpAdapterInterface
     */
    private $httpAdapter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        $this->logger = new NullLogger();

        $this->httpAdapter = new CurlHttpAdapter();
        $this->httpAdapter->getConfiguration()->setKeepAlive(true);
    }

    public function setFirebaseSecret($secret)
    {
        $this->firebaseSecret = $secret;

        return $this;
    }

    public function getFirebaseSecret()
    {
        if (!$this->hasFirebaseSecret()) {
            throw ConfigurationException::noSecretAvailable();
        }

        return $this->firebaseSecret;
    }

    public function hasFirebaseSecret()
    {
        return !!($this->firebaseSecret);
    }

    public function setHttpAdapter(HttpAdapterInterface $httpAdapter)
    {
        $this->httpAdapter = $httpAdapter;

        return $this;
    }

    public function getHttpAdapter()
    {
        return $this->httpAdapter;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    public function getLogger()
    {
        return $this->logger;
    }
}
