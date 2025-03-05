<x-web.inputs.sort.form>
    @section('panel') <input hidden name="panel" value="users"> @endsection
    @section('sort') <x-web.inputs.sort.user.sort /> @endsection
    @section('search') <x-web.inputs.sort.user.search /> @endsection
</x-web.inputs.sort.form>

<table>
    <tr>
        <td class="title">ID</td>
        <td>Логин</td>
        <td>Почта</td>
        <td class="hide">Роль</td>
        <td>Дата регистрации</td>
        <td>Действия</td>
    </tr>
    @foreach($users as $user)
        <tr>
            <td class="title">{{ $user->id }}</td>
            <td><a class="blue" href="{{ route('users.index', ['id' => $user->id]) }}">{{ $user->login }}</a></td>
            <td>{{ $user->email }}</td>
            <td class="hide">{{ $user->user_type }}</td>
            <td>{{ $user->created_at->day }}.{{ $user->created_at->month }}.{{ $user->created_at->year }}</td>
            <td><a class="red" href="{{ route('admin.user.forceDelete', ['id' => $user->id]) }}">Удалить</a></td>
        </tr>
    @endforeach
</table>

{{ $users->links() }}
