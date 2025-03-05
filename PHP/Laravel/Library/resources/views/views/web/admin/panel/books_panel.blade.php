@extends('layouts/app')

@section('title')
    Панель Администратора
@endsection

@section('content')
    <x-web.admin.panel.nav />
    <x-web.admin.panel.book_panel :books="$books" />
@endsection
