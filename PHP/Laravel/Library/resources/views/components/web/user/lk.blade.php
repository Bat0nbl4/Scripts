<div class="info">
    <h1 class="full-w title blue no-h-margin">Личный кабинет</h1>
    <span class="blue full-w title">Вы вошли как <b>{{ $user->login }}</b></span>
    @isset($comment)<p class="green_panel title">{{ $comment }}</p>@endisset

    <form id="data" style="display: flex" class="full-w small_content" method="post" action="{{ route('users.update') }}">
        @method('put')
        @csrf
        <span class="blue">Изменить данные:</span>
        <input name="id" value="{{ $user->id }}" hidden>
        <div class="entry">
            <input type="text" value="{{ $user->login }}" name="login" placeholder="Логин">
            @error('login')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>
        <div class="entry">
            <input type="email" value="{{ $user->email }}" name="email" placeholder="Эл. Почта">
            @error('email')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>
        <nav class="inline">
            <button type="submit" class="button full-w">Изменить</button>
            <a onclick="show('data', 'password')" class="button full-w title">Измениь пароль</a>
        </nav>
    </form>
    <form id="password" class="full-w disable small_content" method="post" action="{{ route('users.update_password') }}">
        @method('put')
        @csrf
        <span class="blue">Изменить пароль:</span>
        <input name="id" value="{{ $user->id }}" hidden>
        <div class="entry">
            <input type="password" name="old_password" placeholder="Старый пароль">
            @error('login')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>
        <div class="entry">
            <input type="password" name="password" placeholder="Новый пароль">
            @error('login')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>
        <div class="entry">
            <input type="password" name="password_confirmation" placeholder="Повтор пароля">
            @error('password_confirmation')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>
        <nav class="inline">
            <button type="submit" class="button full-w">Изменить</button>
            <a onclick="show('data', 'password')" class="button full-w title">Измениь данные</a>
        </nav>
    </form>
    <script>
        function show(show1, show2) {
            data = document.getElementById(show1)
            password = document.getElementById(show2)
            if (data.style.display === 'flex') {
                data.style.display = 'none'
                password.style.display = 'flex'
            } else {
                data.style.display = 'flex'
                password.style.display = 'none'
            }
        }
    </script>
    <h2 class="title full-w blue"></h2>
    <p>Статус: {{ $user->user_type }}</p>
    <p>Дата регистрации: {{ $user->created_at->day }}.{{ $user->created_at->month }}.{{ $user->created_at->year }}</p>
</div>
