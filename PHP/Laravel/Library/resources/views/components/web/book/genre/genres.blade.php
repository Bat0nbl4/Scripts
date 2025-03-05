<h2 class="blue">Список жанров</h2>
<div class="info">
    <div class="list left">
        @foreach($genres as $genre)
            <a class="card" href="{{ route('books.genres.show', ['genre' => $genre->genre]) }}">
                <span class="blue">{{ $genre->genre }}</span>
            </a>
        @endforeach
    </div>
</div>
{{ $genres->links() }}
