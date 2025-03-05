<x-web.inputs.sort.form>
    @section('panel') <input hidden name="panel" value="books"> @endsection
    @section('sort') <x-web.inputs.sort.book.sort /> @endsection
    @section('search') <x-web.inputs.sort.book.search /> @endsection
</x-web.inputs.sort.form>

<table>
    <tr>
        <td class="title">ID</td>
        <td>Название</td>
        <td>Автор</td>
        <td class="hide">Жанр</td>
        <td>Издатель</td>
        <td class="hide">Издание</td>
        <td>Дата публикации</td>
        <td class="hide">Последнеее обновление</td>
        <td>Действия</td>
    </tr>
    @foreach($books as $book)
        <tr>
            <td class="title">{{ $book->id }}</td>
            <td>{{ $book->title }}</td>
            <td>{{ $book->author }}</td>
            <td class="hide">{{ $book->genre }}</td>
            <td>{{ $book->publisher }}</td>
            <td class="hide">{{ $book->edition }}</td>
            <td>{{ $book->created_at->day }}.{{ $book->created_at->month }}.{{ $book->created_at->year }}</td>
            <td class="hide">{{ $book->updated_at->day }}.{{ $book->updated_at->month }}.{{ $book->updated_at->year }}</td>
            <td>
                <a class="blue" href="{{ route('books.book', ['id' => $book->id]) }}">Перейти</a>
                <a class="red" href="{{ route('books.forceDelete', ['id' => $book->id]) }}">Удалить</a>
            </td>
        </tr>
    @endforeach
</table>

{{ $books->links() }}

