<div class="container">
    <div class="row">
        <div class="col-md-6 col-lg-5 col-xl-4 mb-4 d-flex flex-column align-items-center flex-fill">
            <img class="rounded-circle img-fluid shadow-sm mb-2 avatar-big w-100" src="<?php echo \vendor\session\Session::get("user.avatar") != "None" ? \vendor\helpers\Resource::resource("storage/img/".\vendor\session\Session::get("user.avatar")) : \vendor\helpers\Resource::resource("img/base.jpg") ?>">
            <form class="shadow-lg w-100 h-100 p-3 rounded-4" method="POST" action="<?php echo \vendor\routing\Router::route("user.change_avatar"); ?>" enctype="multipart/form-data">
                <div class="">
                    <label for="avatar" class="form-label">Фото профиля</label>
                    <input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
                    <input class="form-control <?php echo \vendor\session\Session::hasFlash("input_errors.avatar") ? "is-invalid" : "" ?>"
                           name="avatar"
                           type="file"
                           id="avatar">
                    <button type="submit" class="btn btn-primary">Изменить</button>
                </div>
                <div>
                    <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("input_errors.avatar") ?></span>
                </div>
            </form>
        </div>
        <div class="col-md-6 col-lg-7 col-xl-8 flex-fill mb-4">
            <form class="mb-4 shadow-lg p-3 rounded-4 h-100" method="POST" action="<?php echo \vendor\routing\Router::route("user.change_data"); ?>">
                <h4 class="text-center mb-3">Личные данные</h4>
                <div class="mb-3">
                    <label for="email" class="form-label">эл. Почта</label>
                    <input type="email"
                           name="email"
                           class="form-control <?php echo \vendor\session\Session::hasFlash("input_errors.old_password") ? "is-invalid" : "" ?>"
                           id="old_password"
                           value="<?php echo \vendor\session\Session::get("user.email"); ?>"
                           disabled>
                    <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("input_errors.old_password") ?></span>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Роль</label>
                    <select name="role"
                            id="role"
                            class="form-select"
                            disabled>
                            <option value="<?php echo $role["id"]?>" selected><?php echo $role["name"] ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Статус</label>
                    <select name="status"
                            id="status"
                            class="form-select <?php echo \vendor\session\Session::hasFlash("input_errors.status") ? "is-invalid" : "" ?>">
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status["id"]?>" <?php echo $status["name"] == \vendor\session\Session::get("user.status") ? "selected" : "" ?>><?php echo $status["name"] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("input_errors.status") ?></span>
                </div>
                <div class="container">
                    <div class="row">
                        <span class="d-block form-text col-sm-8 p-0">Дата регистрации: <?php echo \vendor\helpers\Date::noraml_date(\vendor\session\Session::get("user.created_at"))." ".\vendor\helpers\Date::normal_time(\vendor\session\Session::get("user.created_at"))  ?></span>
                        <button type="submit" class="btn btn-primary col-sm-4">Изменить</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <form class="shadow-lg p-4 rounded-4 " method="POST" action="<?php echo \vendor\routing\Router::route("user.change_password"); ?>">
                <h4 class="text-center mb-3">Смена пароля</h4>
                <div class="mb-3">
                    <label for="old_password" class="form-label">Старый пароль</label>
                    <input type="password"
                           name="old_password"
                           class="form-control <?php echo \vendor\session\Session::hasFlash("input_errors.old_password") ? "is-invalid" : "" ?>"
                           id="old_password">
                    <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("input_errors.old_password") ?></span>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Новый пароль</label>
                    <input type="password"
                           name="password"
                           class="form-control <?php echo \vendor\session\Session::hasFlash("input_errors.password") ? "is-invalid" : "" ?>"
                           id="password">
                    <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("input_errors.password") ?></span>
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Подтвердите новый пароль</label>
                    <input type="password"
                           name="password_confirmation"
                           class="form-control <?php echo \vendor\session\Session::hasFlash("input_errors.password_confirmation") ? "is-invalid" : "" ?>"
                           id="password_confirmation">
                    <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("input_errors.password_confirmation") ?></span>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Изменить</button>
                </div>
            </form>
        </div>
    </div>
</div>