<form class="full-w" action="{{ route('ChangeSortSettings') }}">
    @yield('panel')
    <div>
        @yield('search')
        @yield('sort')
    </div>
    <button class="button left" type="submit">Применить</button>
</form>
