<div class="info">
    @if($log)
        <h3 class="title">ID: {{ $log->id }}</h3>
        <span>Объект: {{ $log->object }}</span>
        <span>ID Объекта: {{ $log->object_id }}</span>
        <div class="white-bg info full-w">
            <p class="">{!! nl2br(e($log->log_info)) !!}</p>
        </div>
        <p>{{ $log->created_at }}</p>
        <br>
        <a class="button" href="{{ route('logs.index', ['id' => $log->id]) }}">Перейти</a>
    @else
        <span class="error title full-w">
            <b>
                NO DATA
            </b>
        </span>
    @endif
</div>
