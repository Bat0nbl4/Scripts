<div class="d-flex justify-content-center">
    <?php if (\vendor\session\Session::hasFlash("success")): ?>
        <div class="col-sm-4 col-lg-3 m-3 bg-success bg-opacity-10 p-2 rounded-3 border border-success border-1 mb-3">
            <span class="text-success"><?php echo \vendor\session\Session::getFlash("success") ?></span>
        </div>
    <?php endif; ?>
</div>

<form method="GET" class="container" action="<?php echo \vendor\routing\Router::route("admin.user.list") ?>">
    <div class="input-group">
        <select name="search_by"
                id="search_by"
                class="form-select">
            <?php foreach ($search_by_list as $key => $value): ?>
                <option value="<?php echo $key ?>" <?php echo $key == \vendor\session\Session::get("admin.user.list.search_by") ? "selected" : "" ?>><?php echo $value ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text"
               name="context"
               class=" form-control"
               value="<?php echo \vendor\session\Session::get("admin.user.list.context") ?? "" ?>"
               id="context">
        <button type="submit" class="btn btn-primary">Поиск</button>
    </div>
</form>

<div class="container-lg">
    <table class="table">
        <thead>
        <tr>
            <th>id</th>
            <th>avatar</th>
            <th>email</th>
            <th>created_at</th>
            <th>role</th>
            <th>status</th>
            <th>actions</th>
        </tr>
        </thead>
        <?php if (!empty($users)): ?>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user["id"] ?></td>
                    <td><img class="avatar rounded-circle" src="<?php echo $user["avatar"] != "None" ? \vendor\helpers\Resource::resource("storage/img/".$user["avatar"]) : \vendor\helpers\Resource::resource("img/base.jpg") ?>"></td>
                    <td><?php echo $user["email"] ?></td>
                    <td><?php echo \vendor\helpers\Date::noraml_date($user["created_at"])." ".\vendor\helpers\Date::normal_time($user["created_at"])  ?></td>
                    <td><?php echo $user["role"] ?></td>
                    <td><?php echo $user["status"] ?></td>
                    <td>
                        <a class="btn btn-primary" href="<?php echo \vendor\routing\Router::route("admin.user.edit", ["id" => $user["id"]]) ?>">Редактировать</a>
                        <a class="btn btn-danger" href="<?php echo \vendor\routing\Router::route("admin.user.delete", ["id" => $user["id"]]) ?>">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        <?php endif; ?>
    </table>
</div>
<?php if (empty($users)): ?>
    <div class="d-flex justify-content-center p-2 mb-3">
        <div class="text-center bg-secondary bg-opacity-10 p-2 rounded-3 border border-secondary border-1 mb-3">
            <span class="text-secondary">Не найденно ни одной записи по данному запросу :(</span>
        </div>
    </div>
<?php endif; ?>