<?php

declare(strict_types=1);

use Kreait\Firebase\Tests\Helpers\Invader;

if (!\function_exists('invade')) {
    function invade(object $object): Invader
    {
        return new Invader($object);
    }
}
