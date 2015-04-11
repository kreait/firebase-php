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

interface TokenGeneratorInterface
{
    /**
     * Returns the Firebase secret.
     *
     * @return string
     */
    public function getSecret();

    /**
     * Enables the debug flag for the next generated token.
     *
     * @return TokenGeneratorInterface
     */
    public function enableDebug();

    /**
     * Disables the debug flag for the next generated token.
     *
     * @return TokenGeneratorInterface
     */
    public function disableDebug();

    /**
     * Creates a new anonymous authentication token.
     *
     * @return string
     */
    public function createAnonymousToken();

    /**
     * Creates a new admin token.
     *
     * @return string
     */
    public function createAdminToken();

    /**
     * Creates a new authentication token.
     *
     * @param int|string $id       The user id.
     * @param string     $provider The authentication provider.
     *
     * @throws \RuntimeException if there was an error during the creation of the token
     *
     * @return string
     */
    public function createToken($id, $provider);
}
