<div class="inline">
    <div class="entry">
        <span>Сортировать по:</span>
        <select name="sort_by">
            <option @selected(session()->get('sort.books.sort_by') == 'id') value="id">ID</option>
            <option @selected(session()->get('sort.books.sort_by') == 'author') value="author">Название</option>
            <option @selected(session()->get('sort.books.sort_by') == 'genre') value="genre">Автор</option>
            <option @selected(session()->get('sort.books.sort_by') == 'publisher') value="publisher">Жанр</option>
            <option @selected(session()->get('sort.books.sort_by') == 'edition') value="edition">Издатель</option>
            <option @selected(session()->get('sort.books.sort_by') == 'created_at') value="created_at">Издание</option>
        </select>
    </div>
    <div class="inline-center entry margin-left">
        <input @checked(session()->get('sort.books.reverse') == 'desc') value="desc" name="reverse" type="checkbox">
        <span class="hide margin-left">Обратная сортировка</span>
        @yield("button")
    </div>
</div>

