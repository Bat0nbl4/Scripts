<?php

namespace controllers\user;

use controllers\Controller;
use vendor\data_base\DB;
use vendor\rendering\View;
use vendor\session\Session;

class UserController extends Controller
{
    public function lk() {
        $role = DB::query()
            ->from("role")
            ->select(["*"])
            ->where("name", "=", Session::get("user.role"))
            ->first();

        $statuses = DB::query()
            ->from("user_status")
            ->select(["*"])
            ->get();

        View::render("user/lk", ["role" => $role, "statuses" => $statuses]);
    }

    public function reg() {
        View::render("user/register");
    }

    public function login() {
        View::render("user/login");
    }
}