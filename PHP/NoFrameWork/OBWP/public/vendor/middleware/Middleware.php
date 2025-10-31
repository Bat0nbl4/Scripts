<?php

namespace vendor\middleware;

abstract class Middleware
{
    abstract public static function handle(): void;
}