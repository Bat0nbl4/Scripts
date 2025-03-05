<?php use App\Enums\UserTypeEnum; ?>

<div class="info">
    <h2 class="title full-w blue">{{ $user->login }}</h2>
    <p>Email: {{ $user->email }}</p>
    <p>Статус: {{ $user->user_type }}</p>
    <p>Дата регистрации: {{ $user->created_at->day }}.{{ $user->created_at->month }}.{{ $user->created_at->year }}</p>

</div>
