<!doctype html>
<html lang="ru">
<?php \vendor\rendering\View::IncludeComponent("base/head") ?>
<body class="d-flex flex-column min-vh-100">
    <?php \vendor\rendering\View::IncludeComponent("base/header") ?>
    <main class="d-flex flex-column flex-fill">
        <div class="d-flex justify-content-center p-2 mb-3">
            <?php if (\vendor\session\Session::hasFlash("success")): ?>
                <div class="text-center bg-success bg-opacity-10 p-2 rounded-3 border border-success border-1 mb-3">
                    <span class="text-success"><?php echo \vendor\session\Session::getFlash("success") ?></span>
                </div>
            <?php endif; ?>
            <?php if (\vendor\session\Session::hasFlash("error")): ?>
                <div class="text-center bg-danger bg-opacity-10 p-2 rounded-3 border border-danger border-1 mb-3">
                    <span class="text-danger"><?php echo \vendor\session\Session::getFlash("error") ?></span>
                </div>
            <?php endif; ?>
        </div>
        <?php \vendor\rendering\View::content() ?>
    </main>
    <?php \vendor\rendering\View::IncludeComponent("base/footer") ?>
</body>
</html>