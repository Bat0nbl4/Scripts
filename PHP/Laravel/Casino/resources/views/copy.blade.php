@if($wallet->type == \App\Enums\WalletTypes::Accumulative->value)
    <form method="post" action="{{ route('wallet.withdraw') }}">
        @csrf
        <input type="number" hidden value="{{ $wallet->id }}">
        <select name="status" id="status">
            @foreach(\App\Enums\OwnerStatus::ChangeableStatuses() as $case)
                <option value="{{ $case->value }}" @if($owner->status === $case->value) selected @endif>{{ $case->value }}</option>
            @endforeach
        </select>
        <button class="btn" type="submit">WITHDRAW FOUNDS</button>
    </form>
@endif
