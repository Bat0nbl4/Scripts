@extends('layouts.app')

@section('title')
    HACK
@endsection

@section('content')
    <script src="{{ mix('js/hack.js') }}"></script>
    <div class="hack @if(session()->get('hack.difficulty') == 'impossible') impossible @endif" id="hack">
        <span class="dotted min50"></span>
        @foreach(mb_str_split($text) as $t)
            <span>{{ $t }}</span>
        @endforeach
        <span class="flex-1 dotted"></span>
    </div>
    <span>{{ session()->get('hack.try_count') }} TRYS LEFT</span>
    <br>
    <form method="POST" action="{{ route('hack.try_password') }}">
        @csrf
        <label for="try">PASSWORD: </label>
        <input type="text" id="try" name="password" class="w100">
        <button type="submit" class="btn">TRY</button>
    </form>
    @if (session('message'))
        <p>
            {{ session('message') }}
        </p>
    @endif
@endsection
