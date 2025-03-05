<form method="post" action="{{ route('users.auth') }}" class="simple_block">
    @csrf
    <h2 class="title">Авторизация</h2>
    <div class="entry">
        <input name="login" type="text" placeholder="Логин">
        @error('login')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
    <div class="entry">
        <input name="password" type="password" placeholder="Пароль">
        @error('password')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
    <button class="button" type="submit">Войти</button>
    <a href="{{ route('users.create_user') }}" class="blue">У меня нет аккаунта</a>
    @if(!auth('admin')->user())
        <a href="{{ route('admin.login') }}" class="blue">Войти как администратор</a>
    @endif

</form>
