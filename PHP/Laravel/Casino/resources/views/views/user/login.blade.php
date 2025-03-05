@extends('layouts.app')

@section('title')
    CREATE USER
@endsection

@section('content')
    <form method="POST" action="{{ route('user.store') }}">
        @csrf
        <label for="login">Login: </label>
        <input type="text" id="login" name="login"><br>

        <label for="password">Password: </label>
        <input type="password" name="password" id="password"><br>

        <label for="password_confirmation">Password confirmation: </label>
        <input type="password" name="password_confirmation" id="password_confirmation"><br>

        <button type="submit" class="btn">Enter</button>
    </form>
    <div class="line"></div>
    <form method="POST" action="{{ route('user.auth') }}">
        @csrf
        <label for="login">Login: </label>
        <input type="text" id="login" name="login"><br>

        <label for="password">Password: </label>
        <input type="password" name="password" id="password"><br>

        <button type="submit" class="btn">Enter</button>
    </form>
@endsection



