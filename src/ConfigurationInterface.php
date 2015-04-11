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

use Ivory\HttpAdapter\HttpAdapterInterface;
use Kreait\Firebase\Auth\TokenGeneratorInterface;
use Psr\Log\LoggerInterface;

interface ConfigurationInterface
{
    /**
     * Sets the Firebase Secret.
     *
     * @param string $secret The Firebase Secret.
     *
     * @return ConfigurationInterface
     */
    public function setFirebaseSecret($secret);

    /**
     * Returns the Firebase Secret.
     *
     * @return string
     */
    public function getFirebaseSecret();

    /**
     * Returns whether a Firebase secret is available or not.
     *
     * @return bool
     */
    public function hasFirebaseSecret();

    /**
     * Sets the authentication token generator.
     *
     * @param TokenGeneratorInterface $authTokenGenerator The generator.
     */
    public function setAuthTokenGenerator(TokenGeneratorInterface $authTokenGenerator);

    /**
     * Returns the authentication token generator.
     *
     * @return TokenGeneratorInterface
     */
    public function getAuthTokenGenerator();

    /**
     * Sets the HTTP Adapter.
     *
     * @param HttpAdapterInterface $httpAdapter The HTTP Adapter.
     *
     * @return ConfigurationInterface
     */
    public function setHttpAdapter(HttpAdapterInterface $httpAdapter);

    /**
     * Returns the HTTP Adapter.
     *
     * @return HttpAdapterInterface
     */
    public function getHttpAdapter();

    /**
     * Sets the logger.
     *
     * @param LoggerInterface $logger The logger.
     *
     * @return ConfigurationInterface
     */
    public function setLogger(LoggerInterface $logger);

    /**
     * Returns the logger.
     *
     * @return LoggerInterface
     */
    public function getLogger();
}
