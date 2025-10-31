<?php

namespace vendor\helpers;

abstract class Resource
{
    public static function resource(string $path) : string {
        $full_path = RESOURCE_PATH."/".$path;
        if (USE_BASE_PATH) {
            $full_path = BASE_PATH.$full_path;
        }
        return $full_path;
    }
}