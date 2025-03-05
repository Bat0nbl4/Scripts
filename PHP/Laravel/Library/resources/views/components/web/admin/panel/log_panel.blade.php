<x-web.inputs.sort.form>
    @section('panel') <input hidden name="panel" value="logs"> @endsection
    @section('sort') <x-web.inputs.sort.logs.sort /> @endsection
    @section('search') <x-web.inputs.sort.logs.search /> @endsection
</x-web.inputs.sort.form>

<table>
    <tr>
        <td class="title">ID</td>
        <td class="hide">Объект</td>
        <td class="title">ID объекта</td>
        <td>Лог</td>
        <td>Дата записи</td>
    </tr>
    @foreach($logs as $log)

        <tr>
            <td class="title">{{ $log->id }}</td>
            <td class="hide">{{ $log->object }}</td>
            <td class="title">{{ $log->object_id }}</td>
            <td><a class="blue" href="{{ route('logs.index', ['id' => $log->id]) }}"><span>{{ $log->log_info }}</span></a></td>
            <td>{{ $log->created_at->day }}.{{ $log->created_at->month }}.{{ $log->created_at->year }}</td>
        </tr>
    @endforeach
</table>

{{ $logs->links() }}
