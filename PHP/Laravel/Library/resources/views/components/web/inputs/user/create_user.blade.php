<?php use App\Enums\UserTypeEnum ?>

<form method="post" action="{{ route('users.store') }}" class="simple_block">
    @csrf
    <h2 class="title">Регистрация</h2>
    <div class="entry">
        <input name="login" type="text" placeholder="Логин">
        @error('login')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
    <div class="entry">
        <input name="email" type="email" placeholder="Эл. почта">
        @error('email')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
    <div class="entry">
        <select name="user_type">
            <option value="{{ UserTypeEnum::User->value }}" >{{ UserTypeEnum::User->value }}</option>
            <option value="{{ UserTypeEnum::Publisher->value }}">{{ UserTypeEnum::Publisher->value }}</option>
        </select>
        @error('user_type')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
    <div class="entry">
        <input name="password" type="password" placeholder="Пароль">
        @error('password')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
    <div class="entry">
        <input name="password_confirmation" type="password" placeholder="Повтор пароля">
        @error('password_confirmation')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
    <button class="button" type="submit">Подтвердить</button>
    <a href="{{ route('users.login') }}" class="blue">У меня уже есть аккаунт</a>
</form>
