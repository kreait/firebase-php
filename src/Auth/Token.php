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

class Token implements TokenInterface
{
    /**
     * @var string
     */
    private $uid;

    /**
     * @var string
     */
    private $provider;

    /**
     * @var string
     */
    private $stringToken;

    public function __construct($uid, $provider, $stringToken)
    {
        $this->uid = $uid;
        $this->provider = $provider;
        $this->stringToken = $stringToken;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function getStringToken()
    {
        return $this->stringToken;
    }
}
