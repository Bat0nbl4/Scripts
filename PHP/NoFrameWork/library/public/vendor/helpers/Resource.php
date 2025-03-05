<?php

namespace vendor\helpers;

abstract class Resource
{
    public static function resource(string $path) : string {
        return RESOURCE_PATH."/".$path;
    }
}