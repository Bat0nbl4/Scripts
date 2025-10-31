<?php

namespace middleware;

use vendor\middleware\Middleware;
use vendor\routing\Router;
use vendor\session\Session;

abstract class UserNotAuthMiddleware extends Middleware
{
    public static function handle(): void
    {
        if (Session::has("user")) {
            Router::redirect(Router::route("index"));
        }
    }
}