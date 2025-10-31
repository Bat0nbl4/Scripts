<?php

namespace controllers\admin;

use controllers\Controller;
use vendor\data_base\DB;
use vendor\rendering\View;
use vendor\session\Session;

class AdminController extends Controller
{
    public function list(string $search_by = "user.id", string $context = "") {
        $users = DB::query()
            ->from("user")
            ->select([
                "user.id",
                "user.email",
                "user.created_at",
                "user.password",
                "user.avatar",
                "role.name as role",
                "user_status.name as status"
            ])
            ->join("role", "user.role_id", "=", "role.id")
            ->join("user_status", "user.status_id", "=", "user_status.id")
            ->where($search_by, "LIKE", "%{$context}%")
            ->get();

        if (!empty($_GET["search_by"]) or !empty($_GET["context"])) {
            Session::set("admin.user.list", $_GET);
        }

        View::render("admin/user/list", [
            "users" => $users,
            "search_by_list" => [
                "user.id" => "id",
                "user.email" => "email",
                "user.created_at" => "created_at",
                "role.name" => "role",
                "user_status.name" => "status"
            ]
        ]);
    }

    public function edit(int $id) {
        $user = DB::query()
            ->from("user")
            ->select([
                "user.id",
                "user.email",
                "user.created_at",
                "user.password",
                "role.name as role",
                "user_status.name as status"
            ])
            ->join("role", "user.role_id", "=", "role.id")
            ->join("user_status", "user.status_id", "=", "user_status.id")
            ->where("user.id", "=", $id)
            ->first();

        $roles = DB::query()
            ->from("role")
            ->select(["*"])
            ->get();

        $statuses = DB::query()
            ->from("user_status")
            ->select(["*"])
            ->get();

        View::render("admin/user/edit", ["user" => $user, "roles" => $roles, "statuses" => $statuses]);
    }
}