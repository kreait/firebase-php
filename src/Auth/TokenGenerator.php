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
namespace Kreait\Firebase\Auth;

use Firebase\Token\TokenException;
use Firebase\Token\TokenGenerator as BaseTokenGenerator;

class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * The Firebase secret.
     *
     * @var string
     */
    private $secret;

    /**
     * The Token Generator provided by Firebase themselves.
     *
     * @var BaseTokenGenerator
     */
    private $generator;

    /**
     * Whether the debug option will be set or not.
     *
     * @var bool
     */
    private $debug;

    /**
     * Initializes the Token Generator.
     *
     * @param string $secret The Firebase app secret.
     * @param bool   $debug  Whether the debug option will be set in generated tokens or not.
     */
    public function __construct($secret, $debug = false)
    {
        $this->secret = $secret;

        $this->generator = new BaseTokenGenerator($this->secret);
        $this->debug = $debug;
    }

    public function enableDebug()
    {
        return new self($this->secret, true);
    }

    public function disableDebug()
    {
        return new self($this->secret, false);
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function createAnonymousToken()
    {
        return $this->createCustomToken(
            'anonymous:'.uniqid('firebasephp_', true),
            ['provider' => 'anonymous']
        );
    }

    public function createAdminToken()
    {
        $options = [
            'debug' => $this->debug,
            'admin' => true,
        ];

        return $this->generator
            ->setOptions($options)
            ->create();
    }

    public function createCustomToken($uid, array $claims = [])
    {
        $options = [
            'debug' => $this->debug
        ];

        $data = array_merge($claims, ['uid' => $uid]);

        try {
            return $this->generator
                ->setOptions($options)
                ->setData($data)
                ->create();
        } catch (TokenException $e) {
            throw new \RuntimeException($e->getMessage(), null, $e);
        }
    }
}
