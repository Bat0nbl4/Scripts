<?php \vendor\rendering\View::title("Регистрация"); ?>

<div class="flex-fill d-flex justify-content-center align-items-center">
    <form class="col-md-5 shadow-lg p-4 rounded-4" method="POST" action="<?php echo \vendor\routing\Router::route("user.store"); ?>" enctype="multipart/form-data">
        <h4 class="text-center mb-3">Регистрация</h4>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email"
                   name="email"
                   class="form-control <?php echo \vendor\session\Session::hasFlash("reg_errors.email") ? "is-invalid" : "" ?>"
                   value="<?php echo \vendor\session\Session::getFlash("old_input.email") ?>"
                   id="email"
                   aria-describedby="emailHelp">
            <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("reg_errors.email") ?></span>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Пароль</label>
            <input type="password"
                   name="password"
                   class="form-control <?php echo \vendor\session\Session::hasFlash("reg_errors.password") ? "is-invalid" : "" ?>"
                   id="password">
            <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("reg_errors.password") ?></span>
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Подтвердите пароль</label>
            <input type="password"
                   name="password_confirmation"
                   class="form-control <?php echo \vendor\session\Session::hasFlash("reg_errors.password_confirmation") ? "is-invalid" : "" ?>"
                   id="password_confirmation">
            <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("reg_errors.password_confirmation") ?></span>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Роль</label>
            <select name="role"
                    id="role"
                    class="form-select"
                    aria-label="Disabled
                    select example"
                    disabled>
                <option selected>Пользователь</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="avatar" class="form-label">Фото профиля</label>
            <input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
            <input class="form-control <?php echo \vendor\session\Session::hasFlash("reg_errors.avatar") ? "is-invalid" : "" ?>"
                   name="avatar"
                   type="file"
                   id="avatar">
            <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("reg_errors.avatar") ?></span>
        </div>
        <div class="mb-3 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Зарегестрироваться</button>
        </div>
        <a class="link text-center d-block" href="<?php echo \vendor\routing\Router::route("user.login") ?>">Войти</a>
    </form>
</div>