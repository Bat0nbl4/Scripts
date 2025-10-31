<?php

namespace vendor\helpers;

abstract class Str
{
    public static function random(int $length = 1) {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    public static function unique_random(int $additional_length = 0, bool $more_entropy = true) {
        return uniqid($more_entropy) . self::random($additional_length);
    }
}