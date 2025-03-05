@extends('layouts.app')

@section('title')
    LK
@endsection

<?php $user = \Illuminate\Support\Facades\Auth::user(); ?>

@section('content')
    <h1>{{ $user->login }}</h1>
    <span>DATA OF REGISTRATION: {{ $user->created_at->day }}.{{ $user->created_at->month }}.{{ $user->created_at->year }}</span><br>
    <div class="flex row gap-5">
        <a href="{{ route('user.logout') }}" class="btn">LOGOUT</a>
        <a href="{{ route('wallet.create') }}" class="btn">CREATE WALLET</a>
        <a href="{{ route('wallet.pay_page') }}" class="btn">REMITTANCE</a>
    </div>
    <div class="line margin-h"></div>
    <h2>WALLETS</h2>
    <table class="table">
        <thead>
            <th>NAME</th>
            <th>TYPE</th>
            <th>VALUE</th>
            <th>STATUS</th>
            <th>EDIT</th>
            <th>SELECT</th>
        </thead>
        <tbody>
        @foreach($wallets as $wallet)
            <tr>
                <td>{{ $wallet->name }}</td>
                <td>{{ $wallet->type }}</td>
                <td class="right-align">{{ $wallet->value }}</td>
                <td>{{ $wallet->status }}</td>
                <td>
                    @if(\App\Models\Owner::where('user_id', \Illuminate\Support\Facades\Auth::id())->whereIn('status', [\App\Enums\OwnerStatus::Creator->value, \App\Enums\OwnerStatus::Owner->value])->where('wallet_id', $wallet->id)->exists())
                        <a class="flex w100 btn" href="{{ route('wallet.edit', ['id' => $wallet->id]) }}">EDIT</a>
                    @else
                        <a class="btn danger" href="{{ route('wallet.remove_owner', ['id' => \App\Models\Owner::where('wallet_id', $wallet->id)->first()->id]) }}">REFUSE</a>
                    @endif
                </td>
                <td>
                    @if(session()->get('wallet.active') == $wallet->id)
                        <span>SELECTED</span>
                    @elseif($wallet->type != \App\Enums\WalletTypes::Accumulative->value and $wallet->status == \App\Enums\WalletStatus::Active->value)
                        <a class="flex w100 btn" href="{{ route('wallet.set_active', ['id' => $wallet->id]) }}">SELECT</a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if (session('message'))
        <p>
            {{ session('message') }}
        </p>
    @endif
@endsection



