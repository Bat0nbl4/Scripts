<div class="inline">
    <div class="entry">
        <span>Искть по:</span>
        <select name="search_by" id="search_by">
            <option @selected(session()->get('sort.books.search_by') == 'id') value="id">ID</option>
            <option @selected(session()->get('sort.books.search_by') == 'title') value="title">Название</option>
            <option @selected(session()->get('sort.books.search_by') == 'author') value="author">Автор</option>
            <option @selected(session()->get('sort.books.search_by') == 'genre') value="genre">Жанр</option>
            <option @selected(session()->get('sort.books.search_by') == 'publisher') value="publisher">Издатель</option>
            <option @selected(session()->get('sort.books.search_by') == 'edition') value="edition">Издание</option>
        </select>
    </div>
    <div class="entry max-w margin-left">
        <input @if(session()->get('sort.books.search_by') == 'edition') class="disable" name="" @else name="search" @endif id="text" type="text" placeholder="Поиск" value="{{ session()->get('sort.books.search') }}">
        <select @if(session()->get('sort.books.search_by') != 'edition') class="disable" name="" @else name="search" @endif id="select">
            @foreach(\App\Enums\BookEditionEnum::cases() as $case)
                <option @selected(session()->get('sort.books.search') == $case->value) value="{{ $case->value }}">{{ $case->value }}</option>
            @endforeach
        </select>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script>
            $('#search_by').change(function(){
                var selectval = $(this).val();
                if ( selectval === 'edition' ) {
                    $('#select').attr('name', 'search');
                    $('#select').show();
                    $('#text').attr('name', '');
                    $('#text').hide();
                } else {
                    $('#text').attr('name', 'search');
                    $('#text').show();
                    $('#select').attr('name', '');
                    $('#select').hide();
                }
            });
            $('#select').change(function (){
                $('#text').attr('value', $(this).val());
            });
        </script>
    </div>
</div>
