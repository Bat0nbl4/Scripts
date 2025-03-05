@extends('layouts/app')

@section('content')
    <x-web.book.author.authors :authors="$authors"/>
@endsection
