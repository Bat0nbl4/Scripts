@extends('layouts/app')

@section('content')
    <x-web.book.genre.genres :genres="$genres"/>
@endsection
