@extends('layouts/app')

@section('title')
    {{ isset($book) ? 'Редактирование книги' : 'Регистрация книги' }}
@endsection

@section('content')
    @if(isset($book))
        <x-web.inputs.book.create_book :book="$book"/>
    @else
        <x-web.inputs.book.create_book/>
    @endif
@endsection
