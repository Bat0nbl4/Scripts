<header>
    <a class="white logo" href="{{ route('index') }}"><h2>TASK v2</h2></a>
    <nav>
        <a class="white" href="{{ route('books.genres.genres') }}">Жанры</a>
        <a class="white" href="{{ route('books.authors.authors') }}">Авторы</a>
    </nav>
    <nav class="left">
        @auth('admin')
            <a class="white" href="{{ route('admin.users_panel') }}"><h4>{{ auth('admin')->user()->login }}</h4></a>
        @endauth
        @auth('web')
            <a class="white" href="{{ route('users.lk') }}"><h4>{{ auth('web')->user()->login }}</h4></a>
        @endauth
        @guest('web')
            <a class="white" href="{{ route('users.login') }}"><h4>Войти</h4></a>
        @endguest
    </nav>
</header>
