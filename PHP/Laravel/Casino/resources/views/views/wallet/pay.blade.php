@extends('layouts.app')

@section('title')
    PFWTW
@endsection

@section('content')
    @if($wallets)
        <form method="POST" action="{{ route('wallet.pay') }}">
            @csrf
            <label for="from_wallet">FROM: </label>
            <select name="from_wallet">
                @foreach($wallets as $wallet)
                    <option value="{{ $wallet->id }}">{{ $wallet->name }}: {{ $wallet->value }}</option>
                @endforeach
            </select>
            @error('from_wallet')
            <span>{{ $message }}</span>
            @enderror
            <br>
            <label for="to_wallet">TO: </label>
            <input type="text" name="to_wallet" value="{{ old('to_wallet') }}">
            @error('tp_wallet')
            <span>{{ $message }}</span>
            @enderror
            <br>
            <label for="pay_value">VALUE: </label>
            <input type="number" name="pay_value" value="{{ old('pay_value') }}">
            @error('pay_value')
            <span>{{ $message }}</span>
            @enderror
            <br>
            <button class="btn" type="submit">PAY</button>
            <br>
            @if(session('success'))
                <span>{{ session('success') }}</span>
            @endif
        </form>
    @else
        <h1>YOU DON`T HAVE WALLETS</h1>
    @endif

@endsection



