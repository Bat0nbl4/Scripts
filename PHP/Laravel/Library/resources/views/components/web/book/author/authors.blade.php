<h2 class="blue">Список авторов</h2>
<div class="info">
    <div class="list left">
        @foreach($authors as $author)
            <a class="card" href="{{ route('books.authors.show', ['author' => $author->author]) }}">
                <span class="blue">{{ $author->author }}</span>
            </a>
        @endforeach
    </div>
</div>
{{ $authors->links() }}
