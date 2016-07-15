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

use Ivory\HttpAdapter\HttpAdapterFactory;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Kreait\Firebase\Auth\TokenGenerator;
use Kreait\Firebase\Auth\TokenGeneratorInterface;
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

    /**
     * @var TokenGeneratorInterface
     */
    private $authTokenGenerator;

    /**
     * @var \Google_Client
     */
    private $googleClient;

    /**
     * @var string
     */
    private $authConfigFilePath;

    public function __construct()
    {
        $this->logger = new NullLogger();

        $this->httpAdapter = HttpAdapterFactory::guess();
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
        return (bool) $this->firebaseSecret;
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

    public function setAuthTokenGenerator(TokenGeneratorInterface $authTokenGenerator)
    {
        $this->authTokenGenerator = $authTokenGenerator;
    }

    public function getAuthTokenGenerator()
    {
        if ($this->authTokenGenerator) {
            return $this->authTokenGenerator;
        }

        if (!$this->hasFirebaseSecret()) {
            throw ConfigurationException::noSecretAvailable();
        }

        $this->authTokenGenerator = new TokenGenerator($this->getFirebaseSecret());

        return $this->authTokenGenerator;
    }

    public function setAuthConfigFile($path)
    {
        $this->authConfigFilePath = $path;
    }

    public function getGoogleClient()
    {
        if ($this->googleClient) {
            return $this->googleClient;
        }

        if (!$this->authConfigFilePath) {
            throw new ConfigurationException('No auth config file provided.');
        }

        $client = new \Google_Client();
        $client->setAuthConfigFile($this->authConfigFilePath);
        $client->addScope([
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/firebase.database'
        ]);

        $this->googleClient = $client;

        return $client;
    }

    public function hasGoogleClient()
    {
        try {
            return (bool) $this->getGoogleClient();
        } catch (\Exception $e) {
            return false;
        }
    }
}
