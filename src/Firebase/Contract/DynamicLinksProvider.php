<?php

declare(strict_types=1);

namespace Kreait\Firebase\Contract;

use Kreait\Firebase\Exception\FirebaseException;

interface DynamicLinksProvider
{
    /**
     * @throws FirebaseException when dynamic links can not be provided for the given domain
     */
    public function dynamicLinks(?string $domain = null): DynamicLinks;
}
