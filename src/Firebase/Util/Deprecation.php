<?php

declare(strict_types=1);

namespace Kreait\Firebase\Util;

class Deprecation
{
    public static function trigger(string $classOrMethod, ?string $replacement = null): void
    {
        $message = "{$classOrMethod} is deprecated.";

        if ($replacement) {
            $message .= " Use {$replacement} instead.";
        }

        \trigger_error($message, E_USER_DEPRECATED);
    }
}
