<?php use App\Enums\UserTypeEnum ?>

@extends('layouts/app')

@section('title')Личный кабинет@endsection

@section('content')
    @isset($comment)
        <x-web.user.lk :user="$user" :comment="$comment"/>
    @else
        <x-web.user.lk :user="$user"/>
    @endisset
    <x-web.user.user_nav :user="$user" />

    @if($user->user_type == UserTypeEnum::Publisher->value)
        <x-web.inputs.sort.form>
            @section('panel') <input hidden name="panel" value="books"> @endsection
            @section('sort') <x-web.inputs.sort.book.sort /> @endsection
            @section('search') <x-web.inputs.sort.book.search /> @endsection
        </x-web.inputs.sort.form>
        <x-web.book.library :books="$books"/>
    @endif
@endsection
