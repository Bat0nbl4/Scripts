@extends('layouts.app')

@section('title')
    EDIT WALLET
@endsection

@section('content')
    <h1>{{ $wallet->name }}</h1>
    <h3>VALUE: {{ $wallet->value }}</h3>
    <span>type: {{ $wallet->type }}</span><br>
    <span>status: {{ $wallet->status }}</span>
    @isset($value)
        <p>PROFIT: {{ $value }}</p>
        <p>SPEED: {{ $speed }} PER HOUR</p>
    @endisset
    @if($wallet->status != \App\Enums\WalletStatus::Blocked->value)
        <div class="flex row gap-5">
            @if(session()->get('wallet.active') == $wallet->id)
                <span>SELECTED</span>
            @elseif($wallet->type != \App\Enums\WalletTypes::Accumulative->value and $wallet->status == \App\Enums\WalletStatus::Active->value)
                <a class="btn" href="{{ route('wallet.set_active', ['id' => $wallet->id]) }}">SELECT</a>
            @endif
            @if($wallet->status == \App\Enums\WalletStatus::Lock->value)
                @if($wallet->type == \App\Enums\WalletTypes::Accumulative->value)
                    <form method="post" action="{{ route('wallet.withdraw') }}">
                        @csrf
                        <input name="wallet_from" type="number" hidden value="{{ $wallet->id }}">
                        <span>WITHDRAW TO: </span>
                        <select name="wallet_to" id="status">
                            @foreach($wallets as $w)
                                <option value="{{ $w->id }}" @if($w->id === $wallet->id) selected @endif>{{ $w->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn" type="submit">WITHDRAW FOUNDS</button>
                    </form>
                @endif
                <a class="btn" href="{{ route('wallet.unlock', ['id' => $wallet->id]) }}">UNLOCK</a>
            @elseif($wallet->status == \App\Enums\WalletStatus::Active->value)
                <a class="btn" href="{{ route('wallet.lock', ['id' => $wallet->id]) }}">LOCK</a>
            @endif
            @if($owner->status == \App\Enums\OwnerStatus::Creator->value)
                <a class="btn danger" href="{{ route('wallet.forceDelete', ['id' => $wallet->id]) }}">DELETE</a>
            @else
                <a class="btn danger" href="{{ route('wallet.remove_owner', ['id' => $owner->id]) }}">REFUSE</a>
            @endif
        </div>
    @endif
    @if($owner->status == \App\Enums\OwnerStatus::Creator->value)
        <div class="line"></div>
        <h2>OWNERS</h2>
        <form method="POST" action="{{ route('wallet.add_owner') }}">
            @csrf
            <input type="number" hidden name="wallet_id" value="{{ $wallet->id }}">
            <label for="user_name">ADD OWNER: </label>
            <input type="text" name="user_login">
            <button type="submit" class="btn">ADD</button>
            @error('user_name')
            <span>{{ $message }}</span>
            @enderror
        </form>
        @foreach($owners as $owner)
            @if(in_array($owner->status, array_column(\App\Enums\OwnerStatus::ChangeableStatuses(), 'value')))
                <form method="post" action="{{ route('wallet.change_owner_status') }}">
                    @csrf
                    <h3>{{ $owner->login }}</h3>
                    <label for="status">STATUS: </label>
                    <input name="id" type="number" hidden value="{{ $owner->id }}">
                    <select name="status" id="status">
                        @foreach(\App\Enums\OwnerStatus::ChangeableStatuses() as $case)
                            <option value="{{ $case->value }}" @if($owner->status === $case->value) selected @endif>{{ $case->value }}</option>
                        @endforeach
                    </select>
                    <button class="btn">CHANGE</button>
                    <a href="{{ route('wallet.remove_owner', ['id' => $owner->id]) }}" class="btn">REMOVE</a>
                </form>
            @endif
        @endforeach
    @endif
    @if(session('message'))
        <div class="line"></div>
        <p>{{ session('message') }}</p>
    @endif
@endsection



