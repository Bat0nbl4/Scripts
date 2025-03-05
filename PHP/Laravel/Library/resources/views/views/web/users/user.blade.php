@extends('layouts/app')

@section('title'){{ $user->login }}@endsection

@section('content')
    <x-web.user.user :user="$user"/>
    @if($books)
        <x-web.inputs.sort.form>
            @section('panel') <input hidden name="panel" value="books"> @endsection
            @section('sort') <x-web.inputs.sort.book.sort /> @endsection
            @section('search') <x-web.inputs.sort.book.search /> @endsection
        </x-web.inputs.sort.form>
        <x-web.book.library :books="$books"/>
    @endif
@endsection
