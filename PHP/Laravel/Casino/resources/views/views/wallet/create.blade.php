@extends('layouts.app')

@section('title')
    CREATE WALLET
@endsection

@section('content')
    <form method="POST" action="{{ route('wallet.store') }}">
        @csrf
        @if(\Illuminate\Support\Facades\Auth::user()->freeWallet == true)
            <p>THE WALLET WILL BE CREATED FOR FREE</p>
        @else
            <p>COST OF CREATION: 50,000.00</p>
        @endif
        <label for="name">Wallet name: </label>
        <input type="text" name="name">
        <select name="type">
            @foreach($types as $type)
                @if(\Illuminate\Support\Facades\Auth::user()->freeWallet != true or $type->value == \App\Enums\WalletTypes::Storage->value)
                    <option value="{{ $type->value }}">{{ $type->value }}</option>
                @endif
            @endforeach
        </select>
        <button type="submit" class="btn">CREATE WALLET</button>
        @error('name')
        <br><span>{{ $message }}</span>
        @enderror
    </form>
    @if(session('message'))
        <span>{{ session('message') }}</span>
    @endif
@endsection



