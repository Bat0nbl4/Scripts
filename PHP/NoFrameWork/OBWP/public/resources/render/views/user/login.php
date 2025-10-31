<?php \vendor\rendering\View::title("Вход"); ?>

<div class="flex-fill d-flex justify-content-center align-items-center">
    <form class="col-md-3 shadow-lg p-4 rounded-4" method="POST" action="<?php echo \vendor\routing\Router::route("user.auth") ?>">
        <h4 class="text-center mb-3">Авторизация</h4>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email"
                   name="email"
                   class="form-control <?php echo \vendor\session\Session::hasFlash("login_errors.email") ? "is-invalid" : "" ?>"
                   value="<?php echo \vendor\session\Session::getFlash("old_input.email") ?>"
                   id="email"
                   aria-describedby="emailHelp">
            <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("login_errors.email") ?></span>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Пароль</label>
            <input type="password"
                   name="password"
                   class="form-control <?php echo \vendor\session\Session::hasFlash("login_errors.password") ? "is-invalid" : "" ?>"
                   id="password">
            <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("login_errors.password") ?></span>
        </div>
        <div class="mb-3 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Войти</button>
        </div>
        <a class="link text-center d-block" href="<?php echo \vendor\routing\Router::route("user.reg") ?>">Регистрация</a>
    </form>
</div>