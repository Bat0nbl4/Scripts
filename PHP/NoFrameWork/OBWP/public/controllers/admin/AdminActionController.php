<?php

namespace controllers\admin;

use controllers\Controller;
use vendor\data_base\DB;
use vendor\helpers\Array_r;
use vendor\routing\Router;
use vendor\session\Session;

class AdminActionController extends Controller
{
    public function put(int $id) {

        $isValidated = true;

        if (empty($_POST["email"])) {
            Session::flash("old_input.email", "Это поле обязательно для заполнения!");
            $isValidated = false;
        } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            Session::flash("old_input.email", "Некорректный адрес почты. Пример: example@gmail.com");
            $isValidated = false;
        } elseif (DB::query()->from("user")->select(["id"])->where("email", "=", $_POST["email"])->first()["id"] != $id) {
            Session::flash("old_input.email", "Такой адресс электронной почты уже зарегестрирован.");
            $isValidated = false;
        }

        if (empty($_POST["role"])) {
            Session::flash("old_input.role", "Это поле обязательно для заполнения!");
            $isValidated = false;
        } elseif (!Array_r::has($_POST["role"], DB::query()->from("role")->select(["*"])->get(), depth: 2)) {
            Session::flash("old_input.role", "Несуществующая роль!");
            $isValidated = false;
        }

        if (empty($_POST["status"])) {
            Session::flash("old_input.status", "Это поле обязательно для заполнения!");
            $isValidated = false;
        } elseif (!Array_r::has($_POST["status"], DB::query()->from("user_status")->select(["*"])->get(), depth: 2)) {
            Session::flash("old_input.status", "Несуществующий статус!");
            $isValidated = false;
        }

        if ($isValidated) {
            DB::query()
                ->from("user")
                ->set([
                    "email" => $_POST["email"],
                    "role_id" => $_POST["role"],
                    "status_id" => $_POST["status"],
                ])
                ->where("id", "=", $id)
                ->update();
            Session::flash("success", "Данные успешно обновлены!");
        }

        Router::redirect(Router::route("admin.user.edit", ["id" => $id]));
    }

    public function delete(int $id) {
        DB::query()
            ->from("user")
            ->where("id", "=", $id)
            ->delete();

        Session::flash("success", "Пользователь #{$id} успешно удалён!");

        Router::redirect(Router::route("admin.user.list"));
    }
}