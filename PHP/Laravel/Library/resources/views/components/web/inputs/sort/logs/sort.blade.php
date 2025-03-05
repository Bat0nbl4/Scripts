<div class="inline">
    <div class="entry">
        <span>Сортировать по:</span>
        <select name="sort_by">
            <option @selected(session()->get('sort.logs.sort_by') == 'id') value="id">ID</option>
            <option @selected(session()->get('sort.logs.sort_by') == 'object') value="object">Объект</option>
            <option @selected(session()->get('sort.logs.sort_by') == 'object_id') value="object_id">ID Объекта</option>
            <option @selected(session()->get('sort.logs.sort_by') == 'created_at') value="created_at">Дата записи</option>
        </select>
    </div>
    <div class="inline-center entry margin-left">
        <input @checked(session()->get('sort.logs.reverse') == 'desc') value="desc" name="reverse" type="checkbox">
        <span class="hide margin-left">Обратная сортировка</span>
        @yield("button")
    </div>
</div>

