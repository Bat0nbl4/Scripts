<?php use App\Enums\UserTypeEnum ?>

<nav class="info inline">
    @if($user->user_type == UserTypeEnum::Publisher->value)
        <a href="{{ route('books.create_book') }}" class="button">Создать книгу</a>
    @endif
    <a href="{{ route('users.index', ['id' => $user->id]) }}" class="button">Просмотреть свой профиль</a>
    <nav class="left inline">
        @if(!auth('admin')->check())
            <a href="{{ route('admin.login') }}" class="button">Войти как администратор</a>
        @endif
        <a href="{{ route('users.logout') }}" class="button red-button">Выйти</a>
    </nav>
</nav>
