@extends('layouts/app')

@section('title')Лог {{ $log->id }}@endsection

@section('content')
    <div class="list no-wrap gap">
        <x-web.log.log :log="$prev_log" />
        <x-web.log.log :log="$log" />
        <x-web.log.log :log="$next_log" />
    </div>
@endsection
