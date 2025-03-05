<div class="inline">
    <div class="entry">
        <span>Сортировать по:</span>
        <select name="sort_by">
            <option @selected(session()->get('sort.users.sort_by') == 'id') value="id">ID</option>
            <option @selected(session()->get('sort.users.sort_by') == 'login') value="login">Логин</option>
            <option @selected(session()->get('sort.users.sort_by') == 'email') value="email">Почта</option>
            <option @selected(session()->get('sort.users.sort_by') == 'user_type') value="user_type">Роль</option>
            <option @selected(session()->get('sort.users.sort_by') == 'created_at') value="created_at">Дата регистрации</option>
        </select>
    </div>
    <div class="inline-center entry margin-left">
        <input @checked(session()->get('sort.users.reverse') == 'desc') value="desc" name="reverse" type="checkbox">
        <span class="hide margin-left">Обратная сортировка</span>
        @yield("button")
    </div>
</div>

