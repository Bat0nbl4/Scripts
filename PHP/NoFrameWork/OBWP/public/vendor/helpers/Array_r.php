<?php

namespace vendor\helpers;

abstract class Array_r
{
    public static function has($needle, $haystack, $strict = false, $depth = 1): bool {
        if ($depth > 0) {
            foreach ($haystack as $item) {
                if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::has($needle, $item, $strict, $depth - 1))) {
                    return true;
                }
            }
        }
        return false;
    }
}