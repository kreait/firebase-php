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

class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * The Token Generator provided by Firebase themselves.
     *
     * @var \Services_FirebaseTokenGenerator
     */
    private $generator;

    /**
     * Initializes the Token Generator
     *
     * @param string $secret The Firebase app secret.
     */
    public function __construct($secret)
    {
        $this->generator = new \Services_FirebaseTokenGenerator($secret);
    }

    public function generateAnonymousToken()
    {
        $uid = uniqid('firebase_');
        $provider = 'anonymous';
        $stringToken = $this->generator->createToken(
            ['uid' => $uid, 'provider' => $provider],
            ['debug' => true]
        );

        return new Token($uid, $provider, $stringToken);
    }
}
