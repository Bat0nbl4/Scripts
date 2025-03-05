<?php
use App\Enums\BookEditionEnum;
use Illuminate\Support\Facades\Auth;
?>

<form method="post" enctype="multipart/form-data" action="{{ isset($book) ? route('books.update') : route('books.store') }}">
    @if(isset($book))
        @method('put')
        <input hidden type="text" name="id" id="id" value="{{ $book->id }}">
    @endif
    @csrf
    <input hidden name="publisher" type="text" value="{{ isset($book) ? $book->publisher : auth()->user()->login }}">
    <h2 class="title">{{ isset($book) ? 'Редактировать книгу' : 'Регистрация книги' }}</h2>
    <div class="entry">
        <input value="{{ isset($book) ? $book->title : '' }}" name="title" type="text" placeholder="Название">
        @error('title')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
    <div class="entry">
        <input value="{{ isset($book) ? $book->author : '' }}" name="author" type="text" placeholder="Автор">
        @error('author')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
    <div class="entry">
        <input value="{{ isset($book) ? $book->genre : '' }}" name="genre" type="text" placeholder="Жанр">
        @error('genre')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
    <div class="entry">
        <select name="edition">
            @foreach(BookEditionEnum::cases() as $key => $label)
                <option @selected(isset($book) ? $label->value == $book->edition->value : null ) value="{{ $label->value }}">{{ $label->value }}</option>
            @endforeach
        </select>
        @error('')
        <span class="error">{{ $message }}</span>
        @enderror
    </div>
    <div class="entry">
        <textarea name="description" type="text" placeholder="Описание">{{ isset($book) ? $book->description : '' }}</textarea>
        @error('description')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
    <x-web.inputs.file/>
    <button type="submit" class="button">Подтверить</button>
</form>
