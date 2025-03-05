<div class="inline">
    <div class="entry">
        <span>Искть по:</span>
        <select name="search_by" id="search_by">
            <option @selected(session()->get('sort.logs.search_by') == 'id') value="id">ID</option>
            <option @selected(session()->get('sort.logs.search_by') == 'object') value="object">Объект</option>
            <option @selected(session()->get('sort.logs.search_by') == 'object_id') value="object_id">ID Объекта</option>
            <option @selected(session()->get('sort.logs.search_by') == 'created_at') value="created_at">Дата записи</option>
        </select>
    </div>
    <div class="entry max-w margin-left">
        <input @if(session()->get('sort.logs.search_by') == 'object') class="disable" name="" @else name="search" @endif id="text" type="text" placeholder="Поиск" value="{{ session()->get('sort.logs.search') }}">
        <select @if(session()->get('sort.logs.search_by') != 'object') class="disable" name="" @else name="search" @endif id="select">
            @foreach(\App\Enums\ObjectTypeEnum::cases() as $case)
                <option @selected(session()->get('sort.logs.search') == $case->value) value="{{ $case->value }}">{{ $case->value }}</option>
            @endforeach
        </select>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script>
            $('#search_by').change(function(){
                var selectval = $(this).val(); // Получим значение из select со значением #participation
                if ( selectval === 'object' ) {
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
