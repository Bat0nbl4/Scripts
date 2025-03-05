<a href="{{ route('books.book', ['id' => $book->id]) }}" class="info card">
    <h2 class="title">{{ $book->title }}</h2>
    @if($book->image != null)
        <div class="img">
            <img src="{{ asset('/storage/' . $book->image) }}">
        </div>
    @else
        <div class="img white-bg">
            <h3>{{ $book->title }}</h3>
            <h4>{{ $book->author }}</h4>
        </div>
    @endif
    <p>Автор: {{ $book->author }}</p>
    <p>Жанр: {{ $book->genre }}</p>
    <p class="description full-h">{{ $book->description }}</p>
    <p>Издатель: {{ $book->publisher }}</p>
    <span class="left">{{ $book->created_at->day }}.{{ $book->created_at->month }}.{{ $book->created_at->year }}</span>
</a>
