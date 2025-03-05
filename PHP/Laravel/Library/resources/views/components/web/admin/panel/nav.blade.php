<div class="info">
    <h1 class="blue title full-w no-h-margin">Панель Администратора</h1>
    <p class="blue title full-w">Вы вошли как: <b>{{ auth()->user()->login }}</b></p>
    <br>
    <nav class="full-w">
        <a class="button" href="{{ route('admin.users_panel') }}">Пользователи</a>
        <a class="button" href="{{ route('admin.books_panel') }}">Книги</a>
        <a class="button" href="{{ route('admin.logs_panel') }}">Логи</a>
        <a class="button red-button left" href="{{ route('admin.logout') }}">Выйти</a>
    </nav>
</div>
