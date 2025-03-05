<header class="flex row gap-5">
    <a href="{{ route('index') }}" class="btn">INDEX</a>
    @guest
        <a href="{{ route('user.login') }}" class="btn">LOGIN</a>
    @endguest
    @auth('web')
        <a href="{{ route('user.lk') }}" class="btn">{{ auth()->user()->login }}</a>
        <a href="{{ route('game.init') }}" class="btn">GAME</a>
        <a href="{{ route('hack.menu') }}" class="btn">HACKING</a>
        @if(\Illuminate\Support\Facades\Auth::user()->type == \App\Enums\UserTypes::Admin->value)
            <a href="{{ route('work.index') }}" class="btn">SYSTEM</a>
        @endif
    @endauth

    @yield('header')
</header>
<br>


