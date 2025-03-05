@extends('layouts.app')

@section('title')
    GET-RND-CARD
@endsection

@section('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <script src="{{ mix('js/game.js') }}"></script>

    <table class="game_table" id="multiplier">
        <tr id="header">
            <td>COMBINATION</td>
            <td id="1" class="select">X1</td>
            <td id="2">X2</td>
            <td id="3">X3</td>
            <td id="4">X4</td>
            <td id="5">X5</td>
        </tr>
        <tr id="pair">
            <td>PAIR</td>
            <td id="1" class="select"></td>
            <td id="2"></td>
            <td id="3"></td>
            <td id="4"></td>
            <td id="5"></td>
        </tr>
        <tr id="two_pairs">
            <td>TWO PAIRS</td>
            <td id="1" class="select"></td>
            <td id="2"></td>
            <td id="3"></td>
            <td id="4"></td>
            <td id="5"></td>
        </tr>
        <tr id="set">
            <td>SET</td>
            <td id="1" class="select"></td>
            <td id="2"></td>
            <td id="3"></td>
            <td id="4"></td>
            <td id="5"></td>
        </tr>
        <tr id="straight">
            <td>STRAIGHT</td>
            <td id="1" class="select"></td>
            <td id="2"></td>
            <td id="3"></td>
            <td id="4"></td>
            <td id="5"></td>
        </tr>
        <tr id="flush">
            <td>FLUSH</td>
            <td id="1" class="select"></td>
            <td id="2"></td>
            <td id="3"></td>
            <td id="4"></td>
            <td id="5"></td>
        </tr>
        <tr id="full_house">
            <td>FULL HOUSE</td>
            <td id="1" class="select"></td>
            <td id="2"></td>
            <td id="3"></td>
            <td id="4"></td>
            <td id="5"></td>
        </tr>
        <tr id="four_of_a_kind">
            <td>FOUR OF A KIND</td>
            <td id="1" class="select"></td>
            <td id="2"></td>
            <td id="3"></td>
            <td id="4"></td>
            <td id="5"></td>
        </tr>
        <tr id="straight_flush">
            <td>STRAIGHT FLUSH</td>
            <td id="1" class="select"></td>
            <td id="2"></td>
            <td id="3"></td>
            <td id="4"></td>
            <td id="5"></td>
        </tr>
        <tr id="flush_royal">
            <td>FLUSH ROYAL</td>
            <td id="1" class="select"></td>
            <td id="2"></td>
            <td id="3"></td>
            <td id="4"></td>
            <td id="5"></td>
        </tr>
    </table>
    <button id="minus" class="btn"><</button>
    <button id="plus" class="btn">></button>
    <span>BET: </span>
    <select name="bet" id="bet">
        <option value="1">1.00</option>
        <option value="10">10.00</option>
        <option value="100">100.00</option>
        <option value="1000">1,000.00</option>
        <option value="5000">5,000.00</option>
    </select>
    <button id="set-bet" class="btn">SET BET</button>
    <span id="message-output"></span><br>
    <span>Money in you wallet: <span id="money"></span></span>
    <div class="flex row">
        <div class="flex gap-5 flex-0">
            <div id="table" class="flex row gap-5">
                @for($i = 1; $i <= 5; $i++)
                    <x-card_cell :num="$i" />
                @endfor
            </div>
            <button id="get-rnd-set" class="btn w100">GET RND SET</button>
        </div>
        <div class="flex-1"></div>
    </div>
    <br>
    <label for="volume-control">VOLUME: </label>
    <input type="range" id="volume-control" min="0" max="1" step="0.01" value="0.9">
    <h3>Combination: <span id="combination-output">none</span></h3>
@endsection


