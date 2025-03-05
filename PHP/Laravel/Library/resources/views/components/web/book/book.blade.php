<?php
use App\Models\Book;
$book = Book::where('id', '=', $id)->first();
?>

<div class="info small_content">
    <h2 class="title">{{ $book->title }}</h2>
    @if($book->image != null)
        <div class="img">
            <img src="{{ asset('/storage/' . $book->image) }}">
        </div>
    @else
        <div class="img">
            <h3>{{ $book->title }}</h3>
            <h4>{{ $book->author }}</h4>
        </div>
    @endif
    <p>Автор: {{ $book->author }}</p>
    <p>Жанр: {{ $book->genre }}</p>
    @if($book->description != '')
        <p class="info white-bg full-w">{!! nl2br(e($book->description)) !!}</p>
    @endif
    <p>Издатель: <a class="blue" href="{{ route('users.index', ['id' => \App\Models\User::where('login', '=', $book->publisher)->first()->id]) }}">{{ $book->publisher }}</a></p>
    <p>Издание: {{ $book->edition }}</p>
    <span class="left">{{ $book->created_at->day }}.{{ $book->created_at->month }}.{{ $book->created_at->year }}</span>
    @if(auth('admin')->check() or auth('web')->check())
        @if(auth('admin')->user() or auth('web')->user()->login == $book->publisher)
            <br>
            <nav class="full-w">
                <a href="{{ route('books.create_book', ['book' => $book]) }}" class="button">Редактировать</a>
                <a href="{{ route('books.forceDelete', ['id' => $book->id]) }}" class="button left red-button">Удалить</a>
            </nav>
        @endif
    @endif
</div>
