@extends('layouts.app')

@section('title')
    HACK MENU
@endsection

@section('content')
    <p>MONEY IN YOUR WALLET: {{ $wallet->value }}</p>

    <table class="table">
        <thead>
            <th>DIFFICULTY</th>
            <th>COST</th>
            <th>PRIZE</th>
            <th>ACTION</th>
        </thead>
        <tbody>
            <tr>
                <td>NORMAL</td>
                <td class="right-align">500</td>
                <td class="text-center">100/1500</td>
                <td><a href="{{ route('hack.init', ['difficulty' => 'normal']) }}" class="btn w100">HACKING</a></td>
            </tr>
            <tr>
                <td>HARD</td>
                <td class="right-align">2500</td>
                <td class="text-center">1000/5000</td>
                <td><a href="{{ route('hack.init', ['difficulty' => 'hard']) }}" class="btn w100">HACKING</a></td>
            </tr>
            <tr>
                <td>IMPOSSIBLE</td>
                <td class="right-align">10000</td>
                <td class="text-center">5000/15000</td>
                <td><a href="{{ route('hack.init', ['difficulty' => 'impossible']) }}" class="btn w100">HACKING</a></td>
            </tr>
        </tbody>
    </table>
    @if (session('message'))
        <p>
            {{ session('message') }}
        </p>
    @endif
@endsection
