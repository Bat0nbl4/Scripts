<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameActionController extends Controller
{
    private function generateCard(Array $cards) {
        $suits = ['c', 'h', 'd', 's'];
        $card_id = $suits[array_rand($suits)].rand(2, 14);
        while (in_array($card_id, $cards, true)) {
            $card_id = $suits[array_rand($suits)].rand(2, 14);
        }
        return $card_id;
    }

    public function get_cards() {
        $wallet = Wallet::find(session()->get('wallet.active'));
        return response()->json(session()->get('game.cards') + ['money' => $wallet->value] + ['rolls' => session()->get('game.rolls')]);
    }

    public function get_bet() {
        return response()->json([
                'bet' => intval(session()->get('game.bet')),
                'multiplier' => intval(session()->get('game.multiplier'))
            ]);
    }

    public function set_bet(Request $request) {
        if (session()->get('game.rolls') == 0) {
            session()->put('game.bet', $request->input('bet'));
            session()->put('game.multiplier', $request->input('multiplier'));
            return response()->json(['message' => 'BET GOT']);
        } else {
            return response()->json(['message' => 'You can\'t change the bet during the game']);
        }
    }

    public function game_over() {
        $wallet = Wallet::find(session()->get('wallet.active'));
        if ($wallet) {
            $bank = session()->get('game.bet') * session()->get('game.multiplier');
        }
        $cards = session()->get('game.cards.on_table');
        $combination = 'none';
        $seniority = [];
        $suit = [];
        for ($i = 1; $i <= 5; $i++) {
            array_push($seniority, substr($cards[$i], 1));
            array_push($suit, $cards[$i][0]);
        }

        $keys = array_values($seniority);
        sort($keys);

        $isSequential = false;
        if (count(array_count_values($seniority)) === 5) {
            // Проверяем последовательность
            $isSequential = true;
            for ($i = 1; $i < count($keys); $i++) {
                if ((int)$keys[$i] != (int)$keys[$i - 1] + 1) {
                    $isSequential = false;
                    break;
                }
            }
        }

        $count = 0;
        foreach (array_count_values($seniority) as $value) {
            if ($value === 2) {
                $count++;
            }
        }

        if ($isSequential and in_array(5, array_count_values($suit)) and array_key_exists(14, array_count_values($seniority))) {
            $combination = 'flush_royal';
        }
        elseif ($isSequential and in_array(5, array_count_values($suit))) {
            $combination = 'straight_flush';
        }
        elseif (in_array(4, array_count_values($seniority))) {
            $combination = 'four_of_a_kind';
        }
        elseif (array_values(array_count_values($seniority)) === [2, 3] or array_values(array_count_values($seniority)) === [3, 2]) {
            $combination = 'full_house';
        }
        elseif (in_array(5, array_count_values($suit))) {
            $combination = 'flush';
        }
        elseif ($isSequential) {
            $combination = 'straight';
        }
        elseif (in_array(3, array_count_values($seniority))) {
            $combination = 'set';
        }
        elseif ($count === 2) {
            $combination = 'two_pairs';
        }
        elseif (array_filter(array_count_values($seniority), function ($value, $key) {
            return $key >= 11 && $value == 2;
        }, ARRAY_FILTER_USE_BOTH)) {
            $combination = 'pair';
        }

        switch ($combination) {
            case 'flush_royal':
                $wallet->value += $bank * 500;
                break;
            case 'straight_flush':
                $wallet->value += $bank * 250;
                break;
            case 'four_of_a_kind':
                $wallet->value += $bank * 50;
                break;
            case 'full_house':
                $wallet->value += $bank * 35;
                break;
            case 'flush':
                $wallet->value += $bank * 10;
                break;
            case 'straight':
                $wallet->value += $bank * 9;
                break;
            case 'set':
                $wallet->value += $bank * 4;
                break;
            case 'two_pairs':
                $wallet->value += $bank * 2;
                break;
            case 'pair':
                $wallet->value += $bank;
                break;
        }

        $wallet->save();
        $this->game_restart();
        return ['combination' => $combination, 'money' => $wallet->value];
    }

    public function game_restart() {
        session()->put('game.cards', ['on_table' => [], 'out_table' => []]);
        session()->put('game.rolls', 0);
    }

    // mining
    public function bring_out(Request $request) {
        $value = (int)$request->input('value');
        if ($value < 300) {
            return response()->json(['message' => "YOU DON'T HAVE ENOUGH MONEY TO PAY THE COMMISSION"]);
        }
        $wallet = Wallet::where('id', session()->get('wallet.active'))->first();
        $wallet->value += $value - 300;
        $wallet->save();
        return response()->json(['message' => "OPERATION WAS COMPLETED SUCCESSFULLY"]);
    }

    public function init() {
        session()->put(
            ['game' => [
                'cards' => [
                    'on_table' => [],
                    'out_table' => []
                    ],
                'rolls' => 0,
                'bet' => 1,
                'multiplier' => 1,
                ]
            ]
        );
        return redirect(route('game.table'));
    }

    public function get_RSet(Request $request) {

        $cards_in_game = [];
        if (session()->get('game.cards.on_table')) {
            $cards_in_game += session()->get('game.cards.on_table');
        }
        if (session()->get('game.cards.out_table')) {
            $cards_in_game += session()->get('game.cards.out_table');
        }

        if (session()->get('game.cards.on_table') == null) {
            $cards = [];
            for ($i = 1; $i <= 5; $i++) {
                $card_id = $this->generateCard($cards_in_game);
                $cards += [$i => $card_id];
                array_push($cards_in_game, $card_id);
                session()->put('game.cards.on_table.'.$i, $card_id);
            }
        } else {
            $cards_to_drop = $request->input('items');
            if ($cards_to_drop != []) {
                for ($i = 1; $i <= 5; $i++) {
                    if (in_array($i, $cards_to_drop, false)) {
                        session()->push('game.cards.out_table', session()->get('game.cards.on_table.'.$i));
                        $card_id = $this->generateCard($cards_in_game);
                        array_push($cards_in_game, $card_id);
                        session()->put('game.cards.on_table.'.$i, $card_id);
                    }
                }
            }
        }

        $array = session()->get('game.cards');
        if (session()->get('game.rolls') === 0) {
            $wallet = Wallet::find(session()->get('wallet.active'));
            $bank = session()->get('game.bet') * session()->get('game.multiplier');
            if ($wallet->value < $bank) {
                return response()->json(['message' => 'INSUFFICIENT FUNDS']);
            } else {
                $wallet->value -= $bank;
                $wallet->save();
                $array['money'] = $wallet->value;
            }
        }
        session()->put('game.rolls', session()->get('game.rolls')+1);
        $array += ['rolls' => session()->get('game.rolls')];
        if (session()->get('game.rolls') >= 2) {
            $array += $this->game_over();
        }
        return response()->json($array);
    }
}
