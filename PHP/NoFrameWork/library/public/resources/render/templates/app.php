<!doctype html>
<html lang="ru">
<?php \vendor\rendering\View::IncludeComponent("base/head") ?>
<body>
    <?php \vendor\rendering\View::IncludeComponent("base/header") ?>
    <main>
        <?php \vendor\rendering\View::content() ?>
    </main>
    <?php \vendor\rendering\View::IncludeComponent("base/footer") ?>
</body>
</html>