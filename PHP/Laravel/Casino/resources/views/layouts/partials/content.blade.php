<div class="content">
    @yield('content')

    @auth('web')
        @if(\Illuminate\Support\Facades\Auth::user()->type == \App\Enums\UserTypes::Admin->value)
            <div class="line w100 margin-h"></div>
            <details open>
                <summary class="btn">SESSION DATA</summary>
                <pre>
                    {{ json_encode(session()->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
                </pre>
            </details>
        @endif
    @endauth

</div>
