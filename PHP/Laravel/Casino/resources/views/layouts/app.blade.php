<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="icon" href="{{ asset('img/chip_logo.svg') }}">
    @yield('meta')
</head>
<body>

@include('layouts.partials.header')

@include('layouts.partials.content')

</body>
</html>
