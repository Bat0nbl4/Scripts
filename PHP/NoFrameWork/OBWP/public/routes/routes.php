<?php

use vendor\routing\Router;

Router::get("/", [\controllers\main\MainController::class, "index"], "index");

Router::group("/user", function () {

    Router::group("", function () { // user is auth
        Router::get("/lk", [\controllers\User\UserController::class, "lk"], "user.lk");
        Router::post("/change_password", [\controllers\User\UserActionController::class, "change_password"], "user.change_password");
        Router::post("/change_data", [\controllers\User\UserActionController::class, "change_data"], "user.change_data");
        Router::post("/change_avatar", [\controllers\User\UserActionController::class, "change_avatar"], "user.change_avatar");
        Router::get("/logout", [\controllers\User\UserActionController::class, "logout"], "user.logout");
    }, [\middleware\UserAuthMiddleware::class]);

    Router::group("", function () { // user is not auth
        Router::get("/registration", [\controllers\user\UserController::class, "reg"], "user.reg");
        Router::get("/login", [\controllers\user\UserController::class, "login"], "user.login");
        Router::post("/store", [\controllers\User\UserActionController::class, "store"], "user.store");
        Router::post("/auth", [\controllers\User\UserActionController::class, "auth"], "user.auth");
    }, [\middleware\UserNotAuthMiddleware::class]);

});

Router::group("/admin", function () {
    Router::group("/user", function () { // user is auth
        Router::get("/list", [\controllers\admin\AdminController::class, "list"], "admin.user.list");
        Router::get("/edit", [\controllers\admin\AdminController::class, "edit"], "admin.user.edit");
        Router::post("/put", [\controllers\admin\AdminActionController::class, "put"], "admin.user.put");
        Router::get("/delete", [\controllers\admin\AdminActionController::class, "delete"], "admin.user.delete");
    });
}, [\middleware\UserAuthMiddleware::class, \middleware\IsAdminMiddleware::class]);

/* --- EXAMPLES

Router::group("/user", function () {
    Router::group("", function () {
        Router::get("/logout", [\controllers\user\UserActionController::class, "logout"], "logout");
        Router::get("/lk", [\controllers\user\UserController::class, "lk"], "lk");
    }, [\middleware\UserAuthMiddleware::class]);

    Router::group("", function () {
        Router::get("/reg", [\controllers\user\UserController::class, "reg"], "reg");
        Router::post("/reg_action", [\controllers\user\UserActionController::class, "reg"], "reg_action");
        Router::get("/login", [\controllers\user\UserController::class, "login"], "login");
        Router::post("/login_action", [\controllers\user\UserActionController::class, "login"], "login_action");
    }, [\middleware\UserNotAuthMiddleware::class]);
});

*/