<?php

namespace middleware;

use vendor\middleware\Middleware;
use vendor\routing\Router;
use vendor\session\Session;

abstract class IsAdminMiddleware extends Middleware
{
    public static function handle(): void
    {
        if (Session::get("user.role") != "Администратор") {
            Session::flash("error", "Error 403");
            Router::redirect(Router::route("index"));
        }
    }
}