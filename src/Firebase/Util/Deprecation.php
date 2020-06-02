<?php

declare(strict_types=1);

namespace Kreait\Firebase\Util;

class Deprecation
{
    /**
     * @noinspection OverridingDeprecatedMethodInspection
     */
    public static function trigger(string $method, ?string $replacement = null): void
    {
        $message = "{$method} is deprecated.";

        if ($replacement) {
            $message .= " Use {$replacement} instead.";
        }

        \trigger_error($message, \E_USER_DEPRECATED);
    }
}
