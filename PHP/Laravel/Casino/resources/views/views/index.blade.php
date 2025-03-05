@extends('layouts.app')

@section('title')
    Главная
@endsection

@section('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <script src="{{ mix('js/clicker.js') }}"></script>

    <span id="clicks-output">0.0</span><br>
    <span >SPEED: </span><span id="speed-output">1</span><br>

    <button class="btn" id="bring-out">BRING OUT</button>
    <span>BRING OUT COST: 300</span>
    <input type="checkbox" id="mining" class="checkbox">
    <label for="mining" class="btn">MINING</label>
    <p id="message-output"></p>
@endsection




