<?php

namespace controllers\user;

use controllers\Controller;
use vendor\data_base\DB;
use vendor\helpers\Array_r;
use vendor\helpers\Resource;
use vendor\helpers\Str;
use vendor\routing\Router;
use vendor\security\SuperHash;
use vendor\session\Session;

class UserActionController extends Controller
{
    public function store() {
        $isValidated = true;

        if (empty($_POST["email"])) {
            Session::flash("reg_errors.email", "Это поле обязательно для заполнения!");
            $isValidated = false;
        } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            Session::flash("reg_errors.email", "Некорректный адрес почты. Пример: example@gmail.com");
            $isValidated = false;
        } elseif (DB::query()->from("user")->where("email", "=", $_POST["email"])->first()) {
            Session::flash("reg_errors.email", "Такой адресс электронной почты уже зарегестрирован.");
            $isValidated = false;
        }

        if (empty($_POST["password"])) {
            Session::flash("reg_errors.password", "Это поле обязательно для заполнения!");
            $isValidated = false;
        } elseif (strlen($_POST["password"]) < 8) {
            Session::flash("reg_errors.password", "Минимальная длина пароля 8 символов.");
            $isValidated = false;
        }

        if ($_POST["password"] != $_POST["password_confirmation"]) {
            Session::flash("reg_errors.password_confirmation", "Пароли не совпадают.");
            $isValidated = false;
        }

        $fileName = "None";
        if ($_FILES["avatar"]["name"] and $isValidated) {
            if ($_FILES["avatar"]["error"] !== UPLOAD_ERR_OK) {
                Session::flash("reg_errors.avatar", "Ошибка при загрузке файла: ".$_FILES["avatar"]["error"]);
                $isValidated = false;
            } elseif (!in_array($_FILES["avatar"]["type"], ["image/jpeg", "image/png"])) {
                Session::flash("reg_errors.avatar", "Файл имеет неразрешённое расширение.");
                $isValidated = false;
            } elseif ($_FILES["avatar"]["size"] >= 2 * 1024 * 1024) {
                Session::flash("reg_errors.avatar", "Размер файла не может быть больше 2 МБ.");
                $isValidated = false;
            }
        }

        if ($_FILES["avatar"]["name"] and $isValidated) {
            $fileName = Str::unique_random(8).".".pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);;
            if (!move_uploaded_file(
                $_FILES['avatar']['tmp_name'],
                substr(Resource::resource("storage/img/").$fileName, 1)
            )) {
                Session::flash("reg_errors.avatar", "Some error!");
                $isValidated = false;
            }
        }

        if ($isValidated) {
            DB::query()
                ->from("user")
                ->set([
                    "email" => $_POST["email"],
                    "password" => SuperHash::hashPassword($_POST["password"]),
                    "created_at" => date('Y-m-d H:i:s'),
                    "avatar" => $fileName,
                    "role_id" => 1,
                    "status_id" => 1
                ])
                ->insert();
            Router::redirect(Router::route("index"));
            return;
        }

        Session::flash("old_input", $_POST);
        Session::removeFlash("old_input.password");
        Session::removeFlash("old_input.password_confirmation");

        Router::redirect(Router::route("user.reg"));
    }

    private static function update_user_session(string $email) {
        $user = DB::query()
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
            ->where("email", "=", $email)
            ->join("role", "user.role_id", "=", "role.id")
            ->join("user_status", "user.status_id", "=", "user_status.id")
            ->first();

        Session::set("user", $user);
        Session::remove("user.password");
        return $user ?? null;
    }

    public function auth() {
        $isValidated = true;

        if (empty($_POST["email"])) {
            Session::flash("login_errors.email", "Это поле обязательно для заполнения!");
            $isValidated = false;
        }
        if (empty($_POST["password"])) {
            Session::flash("login_errors.password", "Это поле обязательно для заполнения!");
            $isValidated = false;
        }

        if ($isValidated) {
            $user = self::update_user_session($_POST["email"]);

            if (!$user or !SuperHash::verifyPassword($_POST["password"], $user["password"])) {
                Session::flash("login_errors.password", "Неверный логин или пароль.");
                $isValidated = false;
            }
        }

        if (!$isValidated) {
            Session::flash("old_input", $_POST);
            Session::removeFlash("old_input.password");
            Session::remove("user");
            Router::redirect(Router::route("index"));
            return;
        }

        Session::clear();
        Session::set("user", $user);
        Session::remove("user.password");

        Router::redirect(Router::route("user.lk"));
    }

    public function change_password() {
        $isValidated = true;

        $user = DB::query()
            ->from("user")
            ->where("id", "=", Session::get("user.id"))
            ->first();

        if (!$user) {
            Session::flash("input_errors.password", "Пользователь не найден");
            $isValidated = false;
        }

        if (empty($_POST["old_password"])) {
            Session::flash("input_errors.old_password", "Это поле обязательно для заполнения!");
            $isValidated = false;
        } elseif (!SuperHash::verifyPassword($_POST["old_password"], $user["password"])) {
            Session::flash("input_errors.old_password", "Неверный пароль.");
            $isValidated = false;
        }

        if (empty($_POST["password"])) {
            Session::flash("input_errors.password", "Это поле обязательно для заполнения!");
            $isValidated = false;
        } elseif (strlen($_POST["password"]) < 8) {
            Session::flash("input_errors.password", "Минимальная длина пароля 8 символов.");
            $isValidated = false;
        } elseif (SuperHash::hashPassword($_POST["password"]) === $user["password"]) {
            Session::flash("input_errors.password", "Старый и новый пароли дожны различаться.");
            $isValidated = false;
        }

        if ($_POST["password"] != $_POST["password_confirmation"]) {
            Session::flash("input_errors.password_confirmation", "Пароли не совпадают.");
            $isValidated = false;
        }

        if ($isValidated) {
            DB::query()
                ->from("user")
                ->where("id", "=", $user["id"])
                ->update([
                    "password" => SuperHash::hashPassword($_POST["password"])
                ]);

            Session::flash("success", "Данные успешно обновлены!");
        }

        Router::redirect(Router::route("user.lk"));
    }

    public function change_data() {
        $isValidated = true;

        if (empty($_POST["status"])) {
            Session::flash("input_errors.status", "Это поле обязательно для заполнения!");
            $isValidated = false;
        } elseif (!Array_r::has($_POST["status"], DB::query()->from("user_status")->select(["*"])->get(), depth: 2)) {
            Session::flash("input_errors.status", "Несуществующий статус!");
            $isValidated = false;
        }

        if (!$isValidated) {
            Session::flash("old_input", $_POST);
        } else {
            DB::query()
                ->from("user")
                ->where("id", "=", Session::get("user.id"))
                ->update([
                    "status_id" => $_POST["status"]
                ]);

            self::update_user_session(Session::get("user.email"));
            Session::flash("success", "Данные успешно обновлены!");
        }

        Router::redirect(Router::route("user.lk"));
    }

    public function change_avatar() {
        $isValidated = true;

        $fileName = "None";
        if ($_FILES["avatar"]["name"] and $isValidated) {
            if ($_FILES["avatar"]["error"] !== UPLOAD_ERR_OK) {
                Session::flash("input_errors.avatar", "Ошибка при загрузке файла: ".$_FILES["avatar"]["error"]);
                $isValidated = false;
            } elseif (!in_array($_FILES["avatar"]["type"], ["image/jpeg", "image/png"])) {
                Session::flash("input_errors.avatar", "Файл имеет неразрешённое расширение.");
                $isValidated = false;
            } elseif ($_FILES["avatar"]["size"] >= 2 * 1024 * 1024) {
                Session::flash("input_errors.avatar", "Размер файла не может быть больше 2 МБ.");
                $isValidated = false;
            }
        }

        if ($_FILES["avatar"]["name"] and $isValidated) {
            $fileName = Str::unique_random(8).".".pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);;
            if (!move_uploaded_file(
                $_FILES['avatar']['tmp_name'],
                substr(Resource::resource("storage/img/").$fileName, 1)
            )) {
                Session::flash("input_errors.avatar", "Some error!");
                $isValidated = false;
            }
            if (!unlink(substr(Resource::resource("storage/img/".Session::get("user.avatar")), 1))) {
                Session::set("ada", "ERROR");
            } else {
                Session::set("ada", "SUCCESS");
            }
        }

        if ($isValidated) {
            DB::query()
                ->from("user")
                ->where("id", "=", Session::get("user.id"))
                ->update([
                    "avatar" => $fileName,
                ]);
            Session::flash("success", "Данные успешно обновлены!");
        }

        self::update_user_session(Session::get("user.email"));
        Router::redirect(Router::route("user.lk"));
    }

    public function logout() {
        Session::clear();
        Router::redirect(Router::route("index"));
    }
}