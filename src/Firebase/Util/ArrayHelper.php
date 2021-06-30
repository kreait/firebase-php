<?php
namespace Kreait\Firebase\Util;

class ArrayHelper
{
    /**
     * Flattens Array
     *
     * @param array<mixed,mixed> $arr
     * @param array<mixed,mixed> $out
     * @return array<mixed,mixed>
     */
    public static function flatten(array $arr, array $out=[]) : array
    {
        foreach ($arr as $item) {
            if (is_array($item)) {
                $out = array_merge($out, self::flatten($item));
            } else {
                $out[] = $item;
            }
        }
        return $out;
    }
}
