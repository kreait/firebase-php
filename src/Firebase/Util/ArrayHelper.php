<?php
namespace Kreait\Firebase\Util;

class ArrayHelper
{
    public static function flatten($arr, $out=[])
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
