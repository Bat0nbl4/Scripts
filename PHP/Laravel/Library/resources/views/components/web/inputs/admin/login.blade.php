<form method="post" action="{{ route('admin.auth') }}" class="simple_block">
    @csrf
    <h2 class="title">Админ авторизация</h2>
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
</form>
