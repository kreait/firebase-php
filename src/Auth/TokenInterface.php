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

interface TokenInterface
{
    /**
     * @return string
     */
    public function getUid();

    /**
     * @return string
     */
    public function getProvider();

    /**
     * @return string
     */
    public function getStringToken();
}
