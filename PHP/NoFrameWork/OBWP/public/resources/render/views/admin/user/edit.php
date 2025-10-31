<?php \vendor\rendering\View::title("Редактирование пользователя"); ?>

<div class="flex-fill d-flex justify-content-center align-items-center">
    <form class="col-md-4 shadow-lg p-4 rounded-4" method="POST" action="<?php echo \vendor\routing\Router::route("admin.user.put", ["id" => $user["id"]]); ?>">
        <h4 class="text-center mb-3">Пользователь #<?php echo $user["id"] ?></h4>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email"
                   name="email"
                   class="form-control <?php echo \vendor\session\Session::hasFlash("input_errors.email") ? "is-invalid" : "" ?>"
                   value="<?php echo \vendor\session\Session::getFlash("old_input.email") ?? $user["email"] ?>"
                   id="email"
                   aria-describedby="emailHelp">
            <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("input_errors.email") ?></span>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Роль</label>
            <select name="role"
                    id="role"
                    class="form-select <?php echo \vendor\session\Session::hasFlash("input_errors.role") ? "is-invalid" : "" ?>">
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo $role["id"]?>" <?php echo $role["name"] == $user["role"] ? "selected" : "" ?>><?php echo $role["name"] ?></option>
                <?php endforeach; ?>
            </select>
            <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("input_errors.role") ?></span>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Статус</label>
            <select name="status"
                    id="status"
                    class="form-select <?php echo \vendor\session\Session::hasFlash("input_errors.status") ? "is-invalid" : "" ?>">
                <?php foreach ($statuses as $status): ?>
                    <option value="<?php echo $status["id"]?>" <?php echo $status["name"] == $user["status"] ? "selected" : "" ?>><?php echo $status["name"] ?></option>
                <?php endforeach; ?>
            </select>
            <span class="form-text text-danger"><?php echo \vendor\session\Session::getFlash("input_errors.status") ?></span>
        </div>
        <div class="mb-3">
            <span class="form-text">Дата регистрации пользователя: <?php echo \vendor\helpers\Date::noraml_date($user["created_at"])." ".\vendor\helpers\Date::normal_time($user["created_at"])  ?></span>
        </div>
        <div class="mb-3 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </form>
</div>