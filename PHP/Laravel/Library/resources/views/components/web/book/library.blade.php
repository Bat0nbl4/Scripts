<div class="list gap">
    @foreach($books as $book)
        <x-web.book.card :book="$book"/>
    @endforeach
</div>
{{ $books->links() }}
