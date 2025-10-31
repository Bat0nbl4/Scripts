<header class="d-flex justify-content-center py-3 border-bottom">
    <ul class="nav nav-pills">
        <li class="nav-item"><a href="<?php echo \vendor\routing\Router::route("index") ?>" class="nav-link">Главная</a></li>
        <?php if (\vendor\session\Session::has("user")): ?>
            <li class="nav-item"><a href="<?php echo \vendor\routing\Router::route("user.lk") ?>" class="nav-link">Личный кабинет</a></li>
            <?php if (\vendor\session\Session::get("user.role") == "Администратор"): ?>
                <li class="nav-item"><a href="<?php echo \vendor\routing\Router::route("admin.user.list") ?>" class="nav-link">Админ панель</a></li>
            <?php endif; ?>
            <li class="nav-item"><a href="<?php echo \vendor\routing\Router::route("user.logout") ?>" class="nav-link text-danger">Выход</a></li>
        <?php else: ?>
            <li class="nav-item"><a href="<?php echo \vendor\routing\Router::route("user.login") ?>" class="nav-link">Войти</a></li>
        <?php endif; ?>
    </ul>
</header>